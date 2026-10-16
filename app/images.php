<?php
declare(strict_types=1);
function store_upload(array $file): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new InvalidArgumentException('No se pudo subir una foto. Revisá el tamaño e intentá nuevamente.');
    if (!is_uploaded_file($file['tmp_name'])) throw new InvalidArgumentException('Archivo de subida inválido.');
    if ($file['size'] > 10 * 1024 * 1024) throw new InvalidArgumentException('Cada foto debe pesar hasta 10 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) throw new InvalidArgumentException('Usá fotos JPG, PNG o WebP. Para HEIC, exportá la foto como JPG.');
    $size = @getimagesize($file['tmp_name']);
    if (!$size || $size[0] * $size[1] > 24000000) throw new InvalidArgumentException('La foto debe tener hasta 24 megapíxeles.');
    $source = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png' => @imagecreatefrompng($file['tmp_name']),
        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
    };
    if (!$source) throw new InvalidArgumentException('La imagen no se pudo leer.');
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name']);
        $orientation = (int)($exif['Orientation'] ?? 1);
        if (in_array($orientation, [2, 4, 5, 7], true)) imageflip($source, IMG_FLIP_HORIZONTAL);
        $angle = match ($orientation) {3, 4 => 180, 5, 6 => -90, 7, 8 => 90, default => 0};
        if ($angle) { $rotated = imagerotate($source, $angle, 0); imagedestroy($source); $source = $rotated; }
    }
    $w = imagesx($source); $height = imagesy($source); $scale = min(1, 1920 / max($w, $height));
    $output = imagecreatetruecolor(max(1, (int)round($w*$scale)), max(1, (int)round($height*$scale)));
    imagefill($output, 0, 0, imagecolorallocate($output, 255, 255, 255));
    imagecopyresampled($output, $source, 0, 0, 0, 0, imagesx($output), imagesy($output), $w, $height);
    $path = 'uploads/projects/' . bin2hex(random_bytes(16)) . '.jpg';
    $ok = imagejpeg($output, ROOT . '/' . $path, 82);
    imagedestroy($source); imagedestroy($output);
    if (!$ok) throw new RuntimeException('No se pudo guardar la foto. Revisá permisos de uploads/projects.');
    chmod(ROOT . '/' . $path, 0644); return $path;
}
