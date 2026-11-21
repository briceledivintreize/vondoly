<?php
/** Authentification de l'administration : deux rôles (admin = tout, editor = contenus). */
declare(strict_types=1);

function current_user(): ?array
{
    static $u = false;
    if ($u !== false) return $u;
    session_boot();
    $id = $_SESSION['uid'] ?? null;
    $u = $id ? row('SELECT id, email, name, role FROM users WHERE id = ? AND active = 1', [$id]) : null;
    return $u;
}

function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }

function require_login(): array
{
    $u = current_user();
    if (!$u) redirect(admin_url('login'));
    // Expiration après 8 h d'inactivité
    if (isset($_SESSION['seen']) && time() - $_SESSION['seen'] > 8 * 3600) { logout(); redirect(admin_url('login')); }
    $_SESSION['seen'] = time();
    return $u;
}

function require_admin(): void
{
    if (!is_admin()) { http_response_code(403); exit(view('admin/layout', ['title' => 'Accès réservé', 'content' => '<div class="card"><h2>Accès réservé</h2><p>Cette rubrique est réservée aux administrateurs.</p></div>'])); }
}

function attempt_login(string $email, string $password): bool
{
    $bucket = 'login:' . hash('sha256', client_ip());
    if (!throttle($bucket, 8, 900)) return false;
    $u = row('SELECT * FROM users WHERE lower(email) = lower(?) AND active = 1', [trim($email)]);
    if (!$u || !password_verify($password, $u['password_hash'])) {
        usleep(random_int(200000, 500000));
        return false;
    }
    if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
        q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $u['id']]);
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $u['id'];
    $_SESSION['seen'] = time();
    q("UPDATE users SET last_login = datetime('now') WHERE id = ?", [$u['id']]);
    audit('connexion');
    return true;
}

function logout(): void
{
    session_boot();
    $_SESSION = [];
    session_destroy();
}

function audit(string $action, string $target = ''): void
{
    q('INSERT INTO audit(user_id, action, target) VALUES(?,?,?)', [current_user()['id'] ?? ($_SESSION['uid'] ?? null), $action, $target]);
}
