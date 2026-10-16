<?php
require __DIR__ . '/../app/bootstrap.php';
require ROOT . '/app/admin-layout.php';
start_session(); if (admin()) redirect('/admin/');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        throttle('login');
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $a = query('SELECT * FROM admins WHERE email = ?', [$email])->fetch();
        $hash = $a['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = password_verify((string)($_POST['password'] ?? ''), $hash);
        if (!$a || !$valid) throw new InvalidArgumentException('El email o la contraseña no son correctos.');
        session_regenerate_id(true);
        $_SESSION = ['admin_id' => (int)$a['id'], 'session_version' => (int)$a['session_version'], 'last_active' => time()];
        redirect('/admin/');
    } catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
}
admin_header('Bienvenida', false);
?>
<p>Entrá para compartir tus últimos trabajos.</p>
<?php if ($error): ?><p class="error" role="alert"><?= h($error) ?></p><?php endif ?>
<form method="post" class="card stack"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>">
<label>Email<input type="email" name="email" autocomplete="username" required maxlength="190" value="<?= h($_POST['email'] ?? '') ?>"></label>
<label>Contraseña<input type="password" name="password" autocomplete="current-password" required maxlength="1024"></label>
<button class="button">Entrar</button><a href="/admin/password.php">Olvidé mi contraseña</a></form>
<?php admin_footer(); ?>
