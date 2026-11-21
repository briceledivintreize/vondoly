<?php
/** Moteur générique : liste, formulaire, enregistrement, tri, masquage, suppression. */
declare(strict_types=1);

function crud_dispatch(string $key, ?string $a, ?string $b, callable $admin): never
{
    $cols = collections();
    $c = $cols[$key] ?? null;
    if (!$c) { http_response_code(404); $admin('Introuvable', '<div class="card"><p>Rubrique inconnue.</p></div>'); }
    if (!empty($c['admin'])) require_admin();
    $c['key'] = $key;
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Photos : chaque emplacement a son format (portrait 4:5, paysage 3:2…)
    $scope = null;
    if (!empty($c['scoped'])) {
        $slot = (string) ($_GET['slot'] ?? $_POST['slot'] ?? '');
        $slots = photo_slots();
        if (!isset($slots[$slot])) redirect(admin_url('photos'));
        $scope = ['slot' => $slot] + $slots[$slot];
        $c['label'] = $scope['label'];
    }

    if ($a === 'sort' && $method === 'POST') {
        $ids = json_decode(file_get_contents('php://input') ?: '[]', true)['ids'] ?? ($_POST['ids'] ?? []);
        foreach (array_values((array) $ids) as $i => $id) q("UPDATE {$c['table']} SET sort = ? WHERE id = ?", [$i + 1, (int) $id]);
        content_touched();
        json_out(['ok' => true]);
    }
    if ($a === null) {
        // Emplacement à photo unique : on va directement au formulaire
        if ($scope && $scope['max'] === 1) {
            $id = val('SELECT id FROM photos WHERE slot = ? ORDER BY sort, id LIMIT 1', [$scope['slot']]);
            redirect(admin_url("c/photos/" . ($id ?: 'new')) . '?slot=' . $scope['slot']);
        }
        $where = $scope ? 'WHERE slot = ' . db()->quote($scope['slot']) : '';
        $items = rows("SELECT * FROM {$c['table']} $where ORDER BY {$c['order']}");
        $admin($c['label'], view('admin/list', ['c' => $c, 'items' => $items, 'scope' => $scope]));
    }
    $id = ctype_digit((string) $a) ? (int) $a : null;
    if ($a !== 'new' && !$id) redirect(admin_url('c/' . $key));
    $item = $id ? row("SELECT * FROM {$c['table']} WHERE id = ?", [$id]) : null;
    if ($id && !$item) redirect(admin_url('c/' . $key));

    if ($b === 'toggle' && $method === 'POST') {
        q("UPDATE {$c['table']} SET visible = 1 - visible WHERE id = ?", [$id]);
        content_touched();
        json_out(['visible' => (int) val("SELECT visible FROM {$c['table']} WHERE id = ?", [$id])]);
    }
    if ($b === 'delete' && $method === 'POST') {
        if ($key === 'users' && $id === (int) current_user()['id']) { flash('error', 'Vous ne pouvez pas supprimer votre propre compte.'); redirect(admin_url('c/users')); }
        $imgs = array_filter(array_map(fn($f) => $f['type'] === 'image' ? ($item[$f['name']] ?? null) : null, $c['fields']));
        if ($key === 'books') q('UPDATE books SET other_edition_id = NULL WHERE other_edition_id = ?', [$id]);
        q("DELETE FROM {$c['table']} WHERE id = ?", [$id]);
        q('DELETE FROM translations WHERE entity = ? AND entity_id = ?', [$c['table'], $id]);
        foreach ($imgs as $m) media_delete((int) $m);
        content_touched(); audit('suppression', "$key #$id");
        flash('ok', ucfirst($c['one']) . ' a été supprimé(e).');
        redirect(admin_url('c/' . $key) . ($scope ? '?slot=' . $scope['slot'] : ''));
    }

    $errors = [];
    if ($method === 'POST') {
        [$newId, $errors] = crud_save($c, $item, $scope);
        if (!$errors) {
            flash('ok', 'Enregistré. Le site public est à jour.');
            $next = ($_POST['_next'] ?? '') === 'list' ? admin_url('c/' . $key) : admin_url("c/$key/$newId");
            if ($scope && $scope['max'] === 1) $next = admin_url('photos');
            redirect($next . ($scope && !($scope['max'] === 1) ? '?slot=' . $scope['slot'] : ''));
        }
        $item = array_merge($item ?? [], $_POST);
    }
    $trs = [];
    if ($id) foreach (rows('SELECT field, lang, value FROM translations WHERE entity = ? AND entity_id = ?', [$c['table'], $id]) as $t) $trs[$t['lang']][$t['field']] = $t['value'];
    $title = $id ? ($item[$c['title']] ?? '') ?: 'Modifier' : 'Ajouter ' . $c['one'];
    $admin($c['label'], view('admin/form', ['c' => $c, 'item' => $item, 'errors' => $errors, 'trs' => $trs, 'scope' => $scope, 'heading' => $title]));
}

/** Valide et enregistre. Renvoie [id, erreurs]. */
function crud_save(array $c, ?array $item, ?array $scope): array
{
    $data = []; $errors = []; $oldMedia = [];
    foreach ($c['fields'] as $f) {
        $n = $f['name'];
        $v = $_POST[$n] ?? null;
        switch ($f['type']) {
            case 'image':
                $ratio = $f['ratio'] === 'slot' ? ($scope['ratio'] ?? null) : ($f['ratio'] ?? null);
                $focus = $f['ratio'] === 'slot' ? ($scope['focus'] ?? 'center') : 'center';
                $alt = trim((string) ($_POST[$n . '_alt'] ?? ''));
                if (!empty($_FILES[$n . '_file']['name'])) {
                    try {
                        $data[$n] = handle_upload($_FILES[$n . '_file'], ['ratio' => $ratio, 'focus' => $focus, 'alt' => $alt]);
                        if (!empty($item[$n])) $oldMedia[] = (int) $item[$n];
                    } catch (Throwable $e) { $errors[$n] = $e->getMessage(); }
                } elseif (!empty($_POST[$n . '_remove']) && !empty($item[$n])) {
                    $data[$n] = null; $oldMedia[] = (int) $item[$n];
                } elseif (!empty($item[$n])) {
                    q('UPDATE media SET alt = ? WHERE id = ?', [$alt, $item[$n]]);
                }
                if (!empty($f['required']) && empty($data[$n] ?? $item[$n] ?? null)) $errors[$n] = 'Image obligatoire.';
                break;
            case 'checkbox': $data[$n] = !empty($v) ? 1 : 0; break;
            case 'number': $data[$n] = ($v === '' || $v === null) ? null : (int) $v; break;
            case 'select': case 'relation':
                $data[$n] = ($v === '' || $v === null) ? null : $v;
                if (isset($f['source']) && $data[$n] !== null) $data[$n] = (int) $data[$n];
                break;
            case 'password':
                if ((string) $v !== '') {
                    if (mb_strlen((string) $v) < 12) $errors[$n] = '12 caractères minimum.';
                    else $data['password_hash'] = password_hash((string) $v, PASSWORD_DEFAULT);
                } elseif (!$item) $errors[$n] = 'Mot de passe obligatoire pour un nouveau compte.';
                break;
            case 'url':
                $v = trim((string) $v);
                if ($v !== '' && !preg_match('~^https?://~i', $v)) $v = 'https://' . $v;
                if ($v !== '' && !filter_var($v, FILTER_VALIDATE_URL)) $errors[$n] = 'Adresse invalide.';
                $data[$n] = $v === '' ? null : $v;
                break;
            case 'email':
                $v = trim((string) $v);
                if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) $errors[$n] = 'Adresse e-mail invalide.';
                $data[$n] = $v === '' ? null : $v;
                break;
            case 'slug': $data[$n] = trim((string) $v) === '' ? null : slugify((string) $v); break;
            default: $v = trim(str_replace("\r\n", "\n", (string) $v)); $data[$n] = $v === '' ? null : $v;
        }
        if (!empty($f['required']) && !in_array($f['type'], ['image', 'password', 'checkbox'], true) && ($data[$n] ?? null) === null) $errors[$n] = 'Champ obligatoire.';
    }
    if (empty($c['no_visible'])) $data['visible'] = !empty($_POST['visible']) ? 1 : 0;
    if (has_note($c['table'])) { $nt = trim((string) ($_POST['note'] ?? '')); $data['note'] = $nt === '' ? null : $nt; }
    if ($scope) $data['slot'] = $scope['slot'];

    // Adresse unique générée depuis le titre
    if (!empty($c['slug_from'])) {
        $base = $data['slug'] ?? slugify((string) ($data[$c['slug_from']] ?? 'element'));
        $slug = $base; $i = 2;
        while (val("SELECT COUNT(*) FROM {$c['table']} WHERE slug = ? AND id <> ?", [$slug, $item['id'] ?? 0])) $slug = $base . '-' . $i++;
        $data['slug'] = $slug;
    }
    if ($c['table'] === 'users') {
        if (!empty($data['email']) && val('SELECT COUNT(*) FROM users WHERE lower(email) = lower(?) AND id <> ?', [$data['email'], $item['id'] ?? 0])) $errors['email'] = 'Cette adresse est déjà utilisée.';
        if ($item && (int) $item['id'] === (int) current_user()['id'] && (($data['role'] ?? 'admin') !== 'admin' || empty($data['active']))) $errors['role'] = 'Vous ne pouvez pas retirer vos propres droits d’administrateur.';
        unset($data['password']);
    }
    if ($errors) {
        // Les images déjà traitées lors de cet envoi ne doivent pas rester orphelines
        foreach ($c['fields'] as $f) if ($f['type'] === 'image' && !empty($data[$f['name']]) && (int) $data[$f['name']] !== (int) ($item[$f['name']] ?? 0)) media_delete((int) $data[$f['name']]);
        return [null, $errors];
    }

    if ($c['table'] === 'books') $data['updated_at'] = date('Y-m-d H:i:s');
    if ($item) { update($c['table'], (int) $item['id'], $data); $id = (int) $item['id']; }
    else {
        if ($c['table'] !== 'users') $data['sort'] = (int) val("SELECT COALESCE(MAX(sort),0) + 1 FROM {$c['table']}");
        $id = insert($c['table'], $data);
    }

    // Lien réciproque entre deux éditions d'un même livre
    if ($c['table'] === 'books') {
        $prev = (int) ($item['other_edition_id'] ?? 0); $now = (int) ($data['other_edition_id'] ?? 0);
        if ($prev && $prev !== $now) q('UPDATE books SET other_edition_id = NULL WHERE id = ? AND other_edition_id = ?', [$prev, $id]);
        if ($now && $now !== $id) q('UPDATE books SET other_edition_id = ? WHERE id = ?', [$id, $now]);
    }
    // Traductions
    foreach ((array) ($_POST['tr'] ?? []) as $lang => $fields) {
        if (!in_array($lang, active_langs(), true) || $lang === default_lang()) continue;
        foreach ((array) $fields as $field => $value) {
            q('INSERT INTO translations(entity, entity_id, field, lang, value) VALUES(?,?,?,?,?) ON CONFLICT(entity, entity_id, field, lang) DO UPDATE SET value = excluded.value',
                [$c['table'], $id, $field, $lang, trim((string) $value)]);
        }
    }
    foreach ($oldMedia as $m) media_delete($m);
    content_touched(); audit($item ? 'modification' : 'création', "{$c['key']} #$id");
    return [$id, []];
}

/** Libellé lisible d'une cellule de liste. */
function crud_cell(array $c, string $col, array $item): string
{
    $f = null;
    foreach ($c['fields'] as $x) if ($x['name'] === $col) $f = $x;
    $v = $item[$col] ?? null;
    if ($f && isset($f['source']) && $v) return (string) val("SELECT {$f['source'][1]} FROM {$f['source'][0]} WHERE id = ?", [$v]);
    if ($f && isset($f['options'])) return (string) ($f['options'][$v] ?? $v);
    if ($col === 'date' || $col === 'date_sort') return fr_date($v, 'd MMM y');
    return plain((string) $v, 90);
}

/** Options d'un champ select/relation. */
function field_options(array $f, ?array $item): array
{
    if (isset($f['options'])) return $f['options'];
    [$table, $col] = $f['source'];
    $sql = "SELECT id, $col AS label FROM $table" . ($f['type'] === 'relation' && $item ? ' WHERE id <> ' . (int) ($item['id'] ?? 0) : '') . " ORDER BY " . ($table === 'books' ? 'year, title' : 'sort, label');
    $o = [];
    foreach (rows($sql) as $r) $o[$r['id']] = $r['label'];
    return $o;
}
