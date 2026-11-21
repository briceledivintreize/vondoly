<?php
/**
 * Traitement des images à l'envoi (section 3.5) :
 *  - redressement EXIF, recadrage automatique au format de l'emplacement ;
 *  - déclinaisons responsives en AVIF, WebP et JPEG, jamais agrandies ;
 *  - conservation d'une version haute définition (dossier de presse).
 */
declare(strict_types=1);

const IMG_ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/avif' => 'avif', 'image/gif' => 'gif'];
const IMG_MAX_BYTES = 20 * 1024 * 1024;

/** Valide et traite un fichier envoyé par formulaire. Renvoie l'id média ou lève une exception lisible. */
function handle_upload(array $file, array $opts = []): int
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(match ($file['error'] ?? 0) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Image trop lourde pour le serveur.',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier reçu.',
            default => 'Envoi interrompu, merci de réessayer.',
        });
    }
    if ($file['size'] > IMG_MAX_BYTES) throw new RuntimeException('Image trop lourde (20 Mo maximum).');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(IMG_ALLOWED[$mime])) throw new RuntimeException('Format non pris en charge. Utilisez JPEG, PNG, WebP ou AVIF.');
    return image_process($file['tmp_name'], $opts + ['name' => pathinfo($file['name'] ?? 'image', PATHINFO_FILENAME)]);
}

function image_load(string $path)
{
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    $im = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($path),
        'image/png' => @imagecreatefrompng($path),
        'image/webp' => @imagecreatefromwebp($path),
        'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
        'image/gif' => @imagecreatefromgif($path),
        default => false,
    };
    if (!$im) throw new RuntimeException('Image illisible ou corrompue.');
    // Redressement selon l'orientation EXIF des photos de téléphone
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($path);
        $o = (int) ($exif['Orientation'] ?? 1);
        if ($o === 3) $im = imagerotate($im, 180, 0);
        elseif ($o === 6) $im = imagerotate($im, -90, 0);
        elseif ($o === 8) $im = imagerotate($im, 90, 0);
    }
    // Aplatissement sur fond clair (transparence PNG)
    $w = imagesx($im); $h = imagesy($im);
    $flat = imagecreatetruecolor($w, $h);
    imagefill($flat, 0, 0, imagecolorallocate($flat, 251, 250, 246));
    imagecopy($flat, $im, 0, 0, 0, 0, $w, $h);
    return $flat;
}

/** Recadre une image GD au ratio demandé ('4:5', '3:2', '16:9', '1:1', ou '1200x630'). */
function image_crop($im, string $ratio, string $focus = 'center')
{
    [$a, $b] = array_map('floatval', preg_split('~[:x]~', $ratio));
    $target = $a / $b;
    $w = imagesx($im); $h = imagesy($im);
    $cur = $w / $h;
    if (abs($cur - $target) < 0.01) return $im;
    if ($cur > $target) { $nw = (int) round($h * $target); $nh = $h; $x = (int) (($w - $nw) / 2); $y = 0; }
    else { $nw = $w; $nh = (int) round($w / $target); $x = 0; $y = (int) ($focus === 'top' ? ($h - $nh) * 0.18 : ($h - $nh) / 2); }
    $c = imagecreatetruecolor($nw, $nh);
    imagecopy($c, $im, 0, 0, $x, $y, $nw, $nh);
    return $c;
}

function image_process(string $path, array $opts = []): int
{
    @ini_set('memory_limit', '512M');
    @set_time_limit(120);
    $im = image_load($path);
    $ow = imagesx($im); $oh = imagesy($im);

    $ratio = $opts['ratio'] ?? null;
    if ($ratio) $im = image_crop($im, $ratio, $opts['focus'] ?? 'center');
    $w = imagesx($im); $h = imagesy($im);

    // Échelle de largeurs : jamais au-delà de l'original (pas d'agrandissement)
    $ladder = $opts['widths'] ?? [480, 960, 1440];
    if ($ratio && str_contains($ratio, 'x')) $ladder = [600, (int) explode('x', $ratio)[0]];
    $widths = array_values(array_filter($ladder, fn($x) => $x < $w));
    $widths[] = min($w, max($ladder));
    $widths = array_values(array_unique($widths));
    sort($widths);

    $dir = 'uploads/' . date('Y');
    if (!is_dir(PUB . '/' . $dir)) mkdir(PUB . '/' . $dir, 0775, true);
    $base = $dir . '/' . bin2hex(random_bytes(4)) . '-' . substr(slugify($opts['name'] ?? 'image'), 0, 40);

    $formats = ['jpg', 'webp'];
    $avif = function_exists('imageavif') && ($opts['avif'] ?? true);
    if ($avif) array_unshift($formats, 'avif');

    $last = null;
    foreach ($widths as $tw) {
        $th = (int) round($h * $tw / $w);
        $r = ($tw === $w) ? $im : imagescale($im, $tw, $th, IMG_BICUBIC);
        imageinterlace($r, true);
        imagejpeg($r, PUB . "/$base-$tw.jpg", 82);
        imagewebp($r, PUB . "/$base-$tw.webp", 80);
        if ($avif) imageavif($r, PUB . "/$base-$tw.avif", 52, 6);
        $last = [$tw, imagesy($r)];
    }

    // Version haute définition conservée (dossier de presse, visionneuse)
    $hd = $w > 2400 ? imagescale($im, 2400, (int) round($h * 2400 / $w), IMG_BICUBIC) : $im;
    imagejpeg($hd, PUB . "/$base-orig.jpg", 88);

    // Couleur dominante (fond d'attente)
    $px = imagecreatetruecolor(1, 1);
    imagecopyresampled($px, $im, 0, 0, 0, 0, 1, 1, $w, $h);
    $rgb = imagecolorat($px, 0, 0);
    $color = sprintf('#%02x%02x%02x', ($rgb >> 16) & 255, ($rgb >> 8) & 255, $rgb & 255);

    return insert('media', [
        'base' => $base, 'widths' => json_encode($widths), 'formats' => json_encode($formats),
        'width' => $last[0], 'height' => $last[1], 'orig_width' => $ow, 'orig_height' => $oh,
        'ratio' => $ratio, 'alt' => $opts['alt'] ?? '', 'color' => $color,
    ]);
}

function media_delete(?int $id): void
{
    if (!$id) return;
    $m = row('SELECT base FROM media WHERE id = ?', [$id]);
    if (!$m) return;
    // Ne supprime pas un média encore utilisé ailleurs
    $used = (int) val('SELECT (SELECT COUNT(*) FROM books WHERE cover_id = :i OR back_id = :i) + (SELECT COUNT(*) FROM photos WHERE media_id = :i)
        + (SELECT COUNT(*) FROM news WHERE image_id = :i) + (SELECT COUNT(*) FROM distinctions WHERE image_id = :i) + (SELECT COUNT(*) FROM press WHERE image_id = :i)', ['i' => $id]);
    if ($used > 0) return;
    foreach (glob(PUB . '/' . $m['base'] . '-*') ?: [] as $f) @unlink($f);
    q('DELETE FROM media WHERE id = ?', [$id]);
}
