<?php
require __DIR__ . '/../app/bootstrap.php'; require ROOT . '/app/admin-layout.php';
start_session(); require_admin();
$projects = query('SELECT p.*, (SELECT COUNT(*) FROM photos WHERE project_id = p.id) AS photo_count, (SELECT path FROM photos WHERE project_id = p.id ORDER BY position, id LIMIT 1) AS cover FROM projects p ORDER BY position, id DESC')->fetchAll();
admin_header('Tus proyectos'); ?>
<div class="heading"><p>Compartí tus trabajos y elegí cuáles mostrar en la web.</p><a class="button" href="/admin/project.php">+ Nuevo proyecto</a></div>
<?php if (!$projects): ?><div class="card empty"><h2>Tu próximo jardín empieza acá</h2><p>Creá tu primer proyecto y agregá sus fotos.</p><a href="/admin/project.php" class="button">Crear proyecto</a></div><?php endif ?>
<div class="projects"><?php foreach ($projects as $p): ?><article class="card project-card">
<?php if ($p['cover']): ?><img class="cover" src="/<?= h($p['cover']) ?>" alt="" loading="lazy"><?php else: ?><div class="cover placeholder">Sin fotos</div><?php endif ?>
<div class="project-card__body"><span class="badge <?= $p['published'] ? 'published' : '' ?>"><?= $p['published'] ? 'Publicado' : 'Borrador' ?></span><h2><?= h($p['title']) ?></h2><p><?= h($p['year']) ?> · <?= h($p['photo_count']) ?> fotos · Orden <?= h($p['position']) ?></p><a class="button secondary" href="/admin/project.php?id=<?= h($p['id']) ?>">Editar proyecto</a></div>
</article><?php endforeach ?></div>
<?php admin_footer(); ?>
