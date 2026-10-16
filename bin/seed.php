<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../app/bootstrap.php';
if ((int)query('SELECT COUNT(*) FROM projects')->fetchColumn() !== 0) exit("No se importó: ya hay proyectos.\n");
db()->beginTransaction();
try {
    for ($i=1; $i<=11; $i++) {
        $path = sprintf('assets/jardin-%02d.jpeg', $i);
        query('INSERT INTO projects (title,description,location,year,published,position) VALUES (?,?,?,?,?,?)', ['Jardín residencial','','',2025,1,$i]);
        $id = (int)db()->lastInsertId();
        query('INSERT INTO photos (project_id,path,alt,position) VALUES (?,?,?,?)', [$id,$path,'Jardín residencial',0]);
    }
    db()->commit(); echo "Se importaron las 11 fotos originales. Revisá los nombres y agrupá los trabajos desde el panel.\n";
} catch (Throwable $e) { db()->rollBack(); throw $e; }
