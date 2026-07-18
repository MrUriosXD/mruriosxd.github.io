<?php
/**
 * Puente seguro de descarga y auto-limpieza
 */

$file = $_GET['file'] ?? '';

// Seguridad elemental: Evitar Directory Traversal (ej. ../../etc/passwd)
$file = basename($file);
$filePath = __DIR__ . '/tmp/' . $file;

if (!empty($file) && file_exists($filePath)) {
    // Definir cabeceras para forzar la descarga en el navegador
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . str_replace('media_', 'download_', $file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    // Limpiar el búfer de salida del sistema para evitar archivos corruptos
    flush();
    
    // Leer el archivo y enviarlo al navegador
    readfile($filePath);
    
    // Eliminar el archivo del servidor una vez entregado
    unlink($filePath);
    exit;
} else {
    http_response_code(404);
    echo "El archivo solicitado ya no existe o ya ha sido descargado.";
}
