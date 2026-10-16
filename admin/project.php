<?php
require __DIR__ . '/../app/bootstrap.php'; require ROOT . '/app/admin-layout.php'; require ROOT . '/app/images.php';
start_session(); require_admin();
$id = (int)($_GET['id'] ?? 0);
$project = $id ? query('SELECT * FROM projects WHERE id = ?', [$id])->fetch() : ['title'=>'', 'description'=>'', 'location'=>'', 'year'=>date('Y'), 'published'=>0, 'position'=>0];
if (!$project) { http_response_code(404); exit('No se encontró el proyecto.'); }
$error = ''; $photos = $id ? project_photos($id) : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $created = []; $originalId = $id;
    try {
        // Serializa ediciones concurrentes para no publicar sin portada ni perder fotos.
        db()->beginTransaction();
        if ($id) {
            if (!query('SELECT id FROM projects WHERE id = ? FOR UPDATE', [$id])->fetch()) throw new InvalidArgumentException('El proyecto ya no existe.');
            $photos = project_photos($id);
        }
        if (($_POST['action'] ?? '') === 'delete') {
            if (!$id) throw new InvalidArgumentException('Proyecto inválido.');
            query('DELETE FROM projects WHERE id = ?', [$id]); db()->commit();
            foreach ($photos as $photo) remove_photo_file($photo['path']);
            flash('Proyecto eliminado.'); redirect('/admin/');
        }
        $project = [
            'title'=>trim((string)($_POST['title'] ?? '')),
            'description'=>trim((string)($_POST['description'] ?? '')),
            'location'=>trim((string)($_POST['location'] ?? '')),
            'year'=>(int)($_POST['year'] ?? 0),
            'published'=>isset($_POST['published']) ? 1 : 0,
            'position'=>(int)($_POST['position'] ?? 0),
        ];
        if ($project['title'] === '' || mb_strlen($project['title']) > 120) throw new InvalidArgumentException('El título debe tener entre 1 y 120 caracteres.');
        if (mb_strlen($project['description']) > 5000 || mb_strlen($project['location']) > 120) throw new InvalidArgumentException('Revisá la extensión de la descripción o ubicación.');
        if ($project['year'] < 1900 || $project['year'] > (int)date('Y')+1 || $project['position'] < 0 || $project['position'] > 9999) throw new InvalidArgumentException('Revisá el año y el orden del proyecto.');
        $remove = array_map('intval', (array)($_POST['remove'] ?? []));
        $remaining = array_values(array_filter($photos, fn($p)=>!in_array((int)$p['id'], $remove, true)));
        $upload = $_FILES['photos'] ?? null; $count = 0;
        if ($upload) {
            if (!is_array($upload['error'])) throw new InvalidArgumentException('Formato de subida inválido.');
            foreach ($upload['error'] as $i=>$code) {
                if ($code === UPLOAD_ERR_NO_FILE) continue;
                if (++$count > 10) throw new InvalidArgumentException('Subí hasta 10 fotos por vez.');
                $created[] = store_upload(['error'=>$code,'tmp_name'=>$upload['tmp_name'][$i], 'size'=>$upload['size'][$i]]);
            }
        }
        if (count($remaining)+count($created) > 30) throw new InvalidArgumentException('Cada proyecto puede tener hasta 30 fotos.');
        if ($project['published'] && !$remaining && !$created) throw new InvalidArgumentException('Agregá al menos una foto para publicar.');
        $values = array_values($project);
        if ($id) query('UPDATE projects SET title=?, description=?, location=?, year=?, published=?, position=? WHERE id=?', [...$values, $id]);
        else { query('INSERT INTO projects (title, description, location, year, published, position) VALUES (?,?,?,?,?,?)', $values); $id = (int)db()->lastInsertId(); }
        $cover = (int)($_POST['cover'] ?? 0);
        foreach ($remaining as $photo) {
            $alt = trim((string)($_POST['alt'][$photo['id']] ?? ''));
            $position = (int)($_POST['order'][$photo['id']] ?? 0);
            if (mb_strlen($alt) > 180 || $position < 0 || $position > 9999) throw new InvalidArgumentException('Revisá el texto alternativo y orden de las fotos.');
            query('UPDATE photos SET alt=?, position=? WHERE id=? AND project_id=?', [$alt, $position+1, $photo['id'], $id]);
        }
        // La portada siempre ocupa el primer lugar; el resto conserva el orden elegido.
        if ($cover) query('UPDATE photos SET position=0 WHERE id=? AND project_id=?', [$cover, $id]);
        foreach ($created as $i=>$path) query('INSERT INTO photos (project_id,path,alt,position) VALUES (?,?,?,?)', [$id,$path,$project['title'],10001+$i]);
        foreach ($photos as $photo) if (in_array((int)$photo['id'], $remove, true)) query('DELETE FROM photos WHERE id=? AND project_id=?', [$photo['id'],$id]);
        db()->commit();
        foreach ($photos as $photo) if (in_array((int)$photo['id'], $remove, true)) remove_photo_file($photo['path']);
        flash($project['published'] ? 'Proyecto publicado. Ya se ve en la web.' : 'Borrador guardado. Todavía no se muestra en la web.');
        redirect('/admin/project.php?id='.$id);
    } catch (Throwable $e) {
        if (db()->inTransaction()) db()->rollBack();
        $id = $originalId;
        foreach ($created as $path) remove_photo_file($path);
        if (!$e instanceof InvalidArgumentException) error_log((string)$e);
        $error = $e instanceof InvalidArgumentException ? $e->getMessage() : 'No se pudo guardar. Tus cambios no se aplicaron; intentá nuevamente.';
    }
}
admin_header($id ? 'Editar proyecto' : 'Nuevo proyecto'); ?>
<a class="back" href="/admin/">← Todos los proyectos</a>
<?php if ($error): ?><p class="error" role="alert"><?= h($error) ?> Si seleccionaste fotos nuevas, volvé a seleccionarlas.</p><?php endif ?>
<form method="post" enctype="multipart/form-data" class="editor" id="projectForm">
<input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="MAX_FILE_SIZE" value="10485760">
<div class="stack"><section class="card stack"><h2>Sobre el trabajo</h2>
<label>Nombre del proyecto<input name="title" maxlength="120" required value="<?= h($project['title']) ?>" placeholder="Por ejemplo: Jardín en Carrasco"></label>
<label>Descripción<textarea name="description" rows="5" maxlength="5000" placeholder="Contá cómo transformaste este espacio."><?= h($project['description']) ?></textarea></label>
<label>Ubicación (opcional)<input name="location" maxlength="120" value="<?= h($project['location']) ?>" placeholder="Ciudad o zona, sin dirección privada"></label>
<div class="fields"><label>Año<input name="year" type="number" min="1900" max="<?= date('Y')+1 ?>" required value="<?= h($project['year']) ?>"></label><label>Orden en la web<input name="position" type="number" min="0" max="9999" required value="<?= h($project['position']) ?>"><small>Los números menores aparecen primero.</small></label></div></section>
<section class="card stack"><h2>Fotos del proyecto</h2><p>JPG, PNG o WebP. Hasta 10 MB y 24 megapíxeles por foto, 10 fotos por subida y 30 por proyecto. Se optimizan automáticamente. Para HEIC, exportá como JPG.</p>
<label class="upload">Agregar fotos<input id="photoInput" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple></label><div id="uploadPreview" class="previews" aria-live="polite"></div>
<div class="photo-grid"><?php foreach ($photos as $i=>$photo): ?><div class="photo"><img src="/<?= h($photo['path']) ?>" alt="<?= h($photo['alt']) ?>" loading="lazy"><label class="check"><input type="radio" name="cover" value="<?= h($photo['id']) ?>" <?= $i===0 ? 'checked' : '' ?>> Usar como portada</label><label>Descripción de la imagen<input name="alt[<?= h($photo['id']) ?>]" maxlength="180" value="<?= h($photo['alt']) ?>"></label><label>Orden<input name="order[<?= h($photo['id']) ?>]" type="number" min="0" max="9999" value="<?= $i ?>"></label><label class="check danger"><input type="checkbox" name="remove[]" value="<?= h($photo['id']) ?>"> Eliminar al guardar</label></div><?php endforeach ?></div></section></div>
<aside class="card stack publish"><h2>Publicación</h2><label class="check"><input type="checkbox" name="published" <?= $project['published'] ? 'checked' : '' ?>> Mostrar en la web</label><p>Desmarcá esta opción para guardar un borrador u ocultar el trabajo.</p><button class="button" type="submit" name="action" value="save">Guardar cambios</button><p id="saveStatus" role="status"></p></aside></form>
<?php if ($id): ?><form method="post" class="delete-form" data-confirm="¿Eliminar este proyecto y todas sus fotos? Esta acción no se puede deshacer."><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><button class="button destructive" name="action" value="delete">Eliminar proyecto</button></form><?php endif ?>
<script src="/admin/admin.js" defer></script><?php admin_footer(); ?>
