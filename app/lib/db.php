<?php
/** Accès base de données (SQLite via PDO). */
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL; PRAGMA busy_timeout = 4000;');
    return $pdo;
}

function db_installed(): bool
{
    return is_file(DB_FILE) && filesize(DB_FILE) > 0;
}

function db_migrate(): void
{
    db()->exec(file_get_contents(APP . '/schema.sql'));
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function rows(string $sql, array $params = []): array { return q($sql, $params)->fetchAll(); }
function row(string $sql, array $params = []): ?array { $r = q($sql, $params)->fetch(); return $r ?: null; }
function val(string $sql, array $params = []) { return q($sql, $params)->fetchColumn(); }

/** Insertion générique ; renvoie l'id créé. */
function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(',', $cols),
        implode(',', array_map(fn($c) => ':' . $c, $cols)));
    q($sql, $data);
    return (int) db()->lastInsertId();
}

function update(string $table, int $id, array $data): void
{
    if (!$data) return;
    $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $data['__id'] = $id;
    q("UPDATE $table SET $set WHERE id = :__id", $data);
}

/* ---------------------------------------------------------------------
 *  Réglages (clé → valeur). Les valeurs complexes sont stockées en JSON.
 * ------------------------------------------------------------------- */
function settings_all(): array
{
    static $cache = null;
    if ($cache === null || isset($GLOBALS['__settings_dirty'])) {
        unset($GLOBALS['__settings_dirty']);
        $cache = [];
        foreach (rows('SELECT key, value FROM settings') as $r) $cache[$r['key']] = $r['value'];
    }
    return $cache;
}

function setting(string $key, $default = null)
{
    $all = settings_all();
    return array_key_exists($key, $all) && $all[$key] !== '' ? $all[$key] : $default;
}

function setting_json(string $key, $default = [])
{
    $v = setting($key);
    if ($v === null) return $default;
    $d = json_decode($v, true);
    return $d ?? $default;
}

function setting_set(string $key, $value): void
{
    if (is_array($value)) $value = json_encode($value, JSON_UNESCAPED_UNICODE);
    q('INSERT INTO settings(key,value) VALUES(?,?) ON CONFLICT(key) DO UPDATE SET value = excluded.value', [$key, (string) $value]);
    $GLOBALS['__settings_dirty'] = true;
}

/** Invalide les caches (dossier de presse PDF…) après toute modification de contenu. */
function content_touched(): void
{
    setting_set('content_version', (string) time());
}
