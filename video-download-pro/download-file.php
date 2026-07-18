<?php
// download_file.php
$file = $_GET['file'] ?? '';

// Seguridad elemental: Evitar Directory Traversal
$file = basename($file);
$filePath = __DIR__ . '/tmp/' . $file;

if (!empty($file) && file_exists($filePath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . str_replace('media_', 'video_', $file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    // Leer y enviar el archivo al búfer de salida
    readfile($filePath);
    
    // Eliminar el archivo del servidor una vez entregado para no acumular basura
    unlink($filePath);
    exit;
} else {
    echo "El archivo no existe o ya ha sido descargado.";
}
