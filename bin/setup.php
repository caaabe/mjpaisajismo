<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../app/bootstrap.php';
$email = strtolower(trim($argv[1] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) exit("Uso: php bin/setup.php email@ejemplo.com [--reset]\n");
foreach (['pdo_mysql', 'gd', 'fileinfo', 'mbstring'] as $extension) {
    if (!extension_loaded($extension)) exit('Falta extensión PHP: '.$extension."\n");
}
$sql = file_get_contents(ROOT . '/database/schema.sql');
foreach (explode(';', $sql) as $statement) if (trim($statement)) db()->exec($statement);
$existing = query('SELECT id FROM admins WHERE email=?', [$email])->fetch();
if ($existing && !in_array('--reset', $argv, true)) exit("La cuenta ya existe. Para cambiar su contraseña usá --reset.\n");
fwrite(STDOUT, "Contraseña nueva (12 a 72 bytes): ");
$tty = function_exists('stream_isatty') && stream_isatty(STDIN);
if ($tty) system('stty -echo');
try { $password = rtrim(fgets(STDIN) ?: '', "\r\n"); }
finally { if ($tty) system('stty echo'); fwrite(STDOUT, "\n"); }
if (strlen($password) < 12 || strlen($password) > 72) exit("Contraseña inválida.\n");
$hash = password_hash($password, PASSWORD_DEFAULT);
if ($existing) {
    query('UPDATE admins SET password_hash=?, session_version=session_version+1 WHERE id=?', [$hash,$existing['id']]);
    query('DELETE FROM password_resets WHERE admin_id=?', [$existing['id']]);
} else query('INSERT INTO admins (email,password_hash) VALUES (?,?)', [$email,$hash]);
echo "Cuenta preparada. Entrá a /admin/.\n";
