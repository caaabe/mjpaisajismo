<?php
function admin_header(string $title, bool $logged = true): void { ?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title><?= h($title) ?> · MJ Paisajismo</title><link rel="icon" href="/favicon.svg"><link rel="stylesheet" href="/admin/admin.css"></head><body>
<header class="top"><a class="brand" href="/admin/">MJ <span>Paisajismo</span></a><nav><a href="/" target="_blank" rel="noopener">Ver web ↗</a><?php if ($logged): ?><form method="post" action="/admin/logout.php"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><button class="link">Cerrar sesión</button></form><?php endif ?></nav></header>
<main class="<?= $logged ? 'workspace' : 'auth' ?>"><p class="eyebrow">Administración</p><h1><?= h($title) ?></h1>
<?php if (isset($_SESSION['flash'])): ?><p class="notice" role="status"><?= h($_SESSION['flash']) ?></p><?php unset($_SESSION['flash']); endif;
}
function admin_footer(): void { echo '</main><footer class="footer">MJ Paisajismo · Tu espacio de trabajo</footer></body></html>'; }
