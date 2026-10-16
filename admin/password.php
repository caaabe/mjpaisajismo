<?php
require __DIR__ . '/../app/bootstrap.php'; require ROOT . '/app/admin-layout.php'; start_session();
$token = (string)($_GET['token'] ?? $_POST['token'] ?? ''); $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        throttle('password');
        if ($token !== '') {
            $password = (string)($_POST['password'] ?? '');
            if (strlen($password) < 12 || strlen($password) > 72) throw new InvalidArgumentException('Usá una contraseña de entre 12 y 72 bytes (evitá muchos emojis).');
            if ($password !== ($_POST['confirmation'] ?? '')) throw new InvalidArgumentException('Las contraseñas no coinciden.');
            db()->beginTransaction();
            $r = query('SELECT * FROM password_resets WHERE token_hash = ? AND expires_at > ? FOR UPDATE', [hash('sha256', $token), time()])->fetch();
            if (!$r) { db()->rollBack(); throw new InvalidArgumentException('El enlace venció o ya se utilizó. Solicitá uno nuevo.'); }
            query('UPDATE admins SET password_hash = ?, session_version = session_version + 1 WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $r['admin_id']]);
            query('DELETE FROM password_resets WHERE admin_id = ?', [$r['admin_id']]); db()->commit();
            $_SESSION = []; flash('Contraseña actualizada. Ya podés entrar.'); redirect('/admin/login.php');
        } else {
            $a = query('SELECT * FROM admins WHERE email = ?', [strtolower(trim((string)($_POST['email'] ?? '')))])->fetch();
            if ($a) {
                $newToken = bin2hex(random_bytes(32));
                query('INSERT INTO password_resets (admin_id, token_hash, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token_hash = VALUES(token_hash), expires_at = VALUES(expires_at)', [$a['id'], hash('sha256', $newToken), time()+1800]);
                $c = config(); $url = rtrim($c['base_url'], '/') . '/admin/password.php?token=' . $newToken;
                $from = $c['mail_from'];
                if (!filter_var($from, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Remitente de correo inválido.');
                if (!mail($a['email'], 'Restablecer acceso a MJ Paisajismo', "Para elegir una contraseña nueva, abrí este enlace (vence en 30 minutos):\n\n" . $url . "\n\nSi no lo solicitaste, ignorá este mensaje.", ['From' => $from, 'Content-Type' => 'text/plain; charset=UTF-8'])) error_log('No se pudo enviar correo de recuperación.');
            }
            flash('Si el email está registrado, te enviamos un enlace. Revisá también correo no deseado.'); redirect('/admin/password.php');
        }
    } catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
}
admin_header($token ? 'Elegí tu contraseña' : 'Recuperar acceso', false); ?>
<?php if ($error): ?><p class="error" role="alert"><?= h($error) ?></p><?php endif ?>
<form method="post" class="card stack"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="token" value="<?= h($token) ?>">
<?php if ($token): ?><label>Nueva contraseña<input type="password" name="password" minlength="12" maxlength="72" autocomplete="new-password" required></label><label>Repetí la contraseña<input type="password" name="confirmation" minlength="12" maxlength="72" autocomplete="new-password" required></label><button class="button">Guardar contraseña</button>
<?php else: ?><label>Tu email<input type="email" name="email" autocomplete="email" required maxlength="190"></label><button class="button">Enviar enlace</button><?php endif ?>
<a href="/admin/login.php">Volver al inicio de sesión</a></form><?php admin_footer(); ?>
