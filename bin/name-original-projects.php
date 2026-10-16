<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../app/bootstrap.php';
$names = require ROOT . '/app/original-projects.php';
$count = 0;
db()->beginTransaction();
try {
    foreach ($names as $i => $title) {
        $path = sprintf('assets/jardin-%02d.jpeg', $i);
        // Solo registros originales de una foto que aún conservan el título genérico.
        $stmt = query("UPDATE projects SET title = ? WHERE title = 'Jardín residencial'
            AND (SELECT COUNT(*) FROM photos WHERE project_id = projects.id) = 1
            AND EXISTS (SELECT 1 FROM photos WHERE project_id = projects.id AND path = ?)", [$title, $path]);
        $count += $stmt->rowCount();
    }
    db()->commit();
    echo "Se actualizaron $count nombres. Los títulos personalizados se conservaron.\n";
} catch (Throwable $e) { db()->rollBack(); throw $e; }
