<?php
declare(strict_types=1);
const ROOT = __DIR__ . '/..';
function config(): array {
    static $config;
    if ($config === null) {
        $path = getenv('MJ_CONFIG') ?: __DIR__ . '/config.local.php';
        if (!is_file($path)) throw new RuntimeException('Falta configurar la conexión. Consultá README.md.');
        $config = require $path;
    }
    return $config;
}
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $c = config();
        $pdo = new PDO($c['dsn'], $c['user'], $c['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
function query(string $sql, array $values = []): PDOStatement {
    $stmt = db()->prepare($sql); $stmt->execute($values); return $stmt;
}
function h(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path, true, 303); exit; }
function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('mj_admin');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/admin', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly' => true, 'samesite' => 'Lax']);
    ini_set('session.use_strict_mode', '1'); session_start();
    header("Content-Security-Policy: default-src 'self'; img-src 'self' blob:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
    header('Cache-Control: no-store'); header('X-Frame-Options: DENY'); header('Referrer-Policy: no-referrer');
}
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function verify_csrf(): void {
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) { http_response_code(403); exit('La sesión del formulario venció. Volvé atrás y recargá la página.'); }
}
function admin(): ?array {
    if (empty($_SESSION['admin_id']) || time() - ($_SESSION['last_active'] ?? 0) > 3600) return null;
    $a = query('SELECT * FROM admins WHERE id = ?', [$_SESSION['admin_id']])->fetch();
    if (!$a || (int)$a['session_version'] !== ($_SESSION['session_version'] ?? 0)) return null;
    $_SESSION['last_active'] = time(); return $a;
}
function require_admin(): array { $a = admin(); if (!$a) redirect('/admin/login.php'); return $a; }
function flash(string $message): void { $_SESSION['flash'] = $message; }
function throttle(string $scope): void {
    $bucket = hash('sha256', $scope . ':' . ($_SERVER['REMOTE_ADDR'] ?? 'cli'));
    query('DELETE FROM auth_attempts WHERE attempted_at < ?', [time() - 3600]);
    if ((int)query('SELECT COUNT(*) FROM auth_attempts WHERE bucket = ? AND attempted_at > ?', [$bucket, time()-900])->fetchColumn() >= 10) {
        throw new InvalidArgumentException('Demasiados intentos. Esperá 15 minutos antes de volver a intentar.');
    }
    query('INSERT INTO auth_attempts (bucket, attempted_at) VALUES (?, ?)', [$bucket, time()]);
}
function project_photos(int $id): array { return query('SELECT * FROM photos WHERE project_id = ? ORDER BY position, id', [$id])->fetchAll(); }
function public_projects(): array {
    $projects = query('SELECT * FROM projects WHERE published = 1 ORDER BY position, id DESC')->fetchAll();
    foreach ($projects as &$project) $project['photos'] = project_photos((int)$project['id']);
    return array_values(array_filter($projects, fn($p) => count($p['photos']) > 0));
}
function remove_photo_file(string $path): void {
    // Nunca borrar assets originales ni aceptar rutas arbitrarias.
    if (preg_match('~^uploads/projects/[a-f0-9]{32}\.jpg$~D', $path)) {
        $file = ROOT . '/' . $path;
        if (is_file($file) && !unlink($file)) error_log('No se pudo eliminar una imagen: ' . $path);
    }
}
set_exception_handler(function (Throwable $e): void {
    error_log((string)$e); http_response_code(503);
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>MJ Paisajismo</title><p>El servicio no está disponible en este momento. Intentá nuevamente en unos minutos.</p></html>';
});
