<?php
/**
 * Sauvegardes : base de données + images envoyées, dans une archive ZIP.
 * Une sauvegarde automatique est créée chaque jour à la première visite de
 * l'administration ; les 14 dernières sont conservées.
 */
declare(strict_types=1);

const BACKUP_DIR = DATA . '/backups';

function backup_create(string $kind = 'auto'): string
{
    if (!is_dir(BACKUP_DIR)) mkdir(BACKUP_DIR, 0775, true);
    @set_time_limit(300);
    $stamp = date('Y-m-d_His');
    $tmpDb = BACKUP_DIR . "/tmp-$stamp.sqlite";
    db()->exec('VACUUM INTO ' . db()->quote($tmpDb));   // copie cohérente même en cours d'utilisation
    $file = BACKUP_DIR . "/sauvegarde-$kind-$stamp.zip";
    $zip = new ZipArchive();
    $zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFile($tmpDb, 'data/site.sqlite');
    $up = PUB . '/uploads';
    if (is_dir($up)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($up, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) if ($f->isFile()) $zip->addFile($f->getPathname(), 'public/uploads/' . str_replace('\\', '/', substr($f->getPathname(), strlen($up) + 1)));
    }
    $zip->addFromString('LISEZMOI.txt', "Sauvegarde du site " . setting('site.domain') . " du " . date('d/m/Y H:i') . ".\nRestauration : remplacer data/site.sqlite et public/uploads par le contenu de cette archive.\n");
    $zip->close();
    @unlink($tmpDb);
    // Rotation : 14 sauvegardes automatiques maximum
    $autos = glob(BACKUP_DIR . '/sauvegarde-auto-*.zip') ?: [];
    rsort($autos);
    foreach (array_slice($autos, 14) as $old) @unlink($old);
    setting_set('backup.last', date('Y-m-d'));
    return $file;
}

function backup_auto(): void
{
    if (setting('backup.last') === date('Y-m-d')) return;
    try { backup_create('auto'); } catch (Throwable $e) { error_log('Sauvegarde automatique impossible : ' . $e->getMessage()); }
}

function backup_list(): array
{
    $files = glob(BACKUP_DIR . '/sauvegarde-*.zip') ?: [];
    rsort($files);
    return array_map(fn($f) => ['name' => basename($f), 'size' => filesize($f), 'date' => filemtime($f)], $files);
}

function backup_path(string $name): ?string
{
    if (!preg_match('~^sauvegarde-(auto|manuel)-[\d_-]+\.zip$~', $name)) return null;
    $f = BACKUP_DIR . '/' . $name;
    return is_file($f) ? $f : null;
}

function backup_download(string $name): never
{
    $f = backup_path($name);
    if (!$f) { http_response_code(404); exit('Fichier introuvable.'); }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Length: ' . filesize($f));
    readfile($f);
    exit;
}

function backup_delete(string $name): void
{
    if ($f = backup_path($name)) @unlink($f);
}
