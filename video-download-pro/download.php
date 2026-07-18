<?php
/**
 * Author: Jorge Urios Ferrando (MrUriosXD)
 * Backend para Video Downloader Pro
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
    exit;
}

// Obtener y decodificar los datos de la petición
$input = json_decode(file_get_contents('php://input'), true);

$url   = $input['url'] ?? '';
$type  = $input['type'] ?? 'all'; // all (mp4), video (solo video mp4), audio (mp3)
$trim  = $input['trim'] ?? false;
$start = $input['start'] ?? '00:00:00';
$end   = $input['end'] ?? '00:01:00';

if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'error' => 'URL no válida o vacía.']);
    exit;
}

// Directorio temporal para procesar las descargas
$outputDir = __DIR__ . '/tmp/';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// Generar un nombre de archivo único para evitar colisiones
$fileId = uniqid('media_', true);
$outputTemplate = $outputDir . $fileId . '.%(ext)s';

// Configuración de comandos básicos según el tipo solicitado
// Nota: Asegúrate de tener 'yt-dlp' y 'ffmpeg' instalados en el PATH de tu servidor.
$ytDlpCmd = "yt-dlp --no-playlist --restrict-filenames ";

switch ($type) {
    case 'audio':
        $ytDlpCmd .= "-x --audio-format mp3 --audio-quality 0 ";
        $expectedExt = 'mp3';
        break;
    case 'video':
        // Descarga solo video (suele venir sin audio si es máxima calidad, o en formato bruto)
        $ytDlpCmd .= "-f \"videoonly\" --merge-output-format mp4 ";
        $expectedExt = 'mp4';
        break;
    case 'all':
    default:
        // Intenta descargar la mejor combinación de video + audio unificada en MP4
        $ytDlpCmd .= "-f \"bv*[ext=mp4]+ba[ext=m4a]/b[ext=mp4]\" --merge-output-format mp4 ";
        $expectedExt = 'mp4';
        break;
}

// Si se solicita recorte, aplicamos los argumentos nativos de yt-dlp usando ffmpeg de fondo
if ($trim) {
    // Sanitizar tiempos para evitar inyecciones de comandos
    $start = preg_replace('/[^0-9:]/', '', $start);
    $end = preg_replace('/[^0-9:]/', '', $end);
    $ytDlpCmd .= "--downloader ffmpeg --downloader-args \"ffmpeg:-ss {$start} -to {$end}\" ";
}

// Añadir la plantilla de salida y escapar la URL para la terminal
$ytDlpCmd .= "-o " . escapeshellarg($outputTemplate) . " " . escapeshellarg($url);

// Ejecutar el comando del sistema
exec($ytDlpCmd . " 2>&1", $outputLines, $returnCode);

if ($returnCode !== 0) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error al procesar el vídeo con yt-dlp.',
        'debug' => implode("\n", $outputLines)
    ]);
    exit;
}

// Localizar el archivo descargado final (ya que las extensiones reales pueden variar sutilmente)
$downloadedFiles = glob($outputDir . $fileId . '.*');
if (empty($downloadedFiles)) {
    echo json_encode(['success' => false, 'error' => 'No se pudo localizar el archivo convertido en el servidor.']);
    exit;
}

$filePath = $downloadedFiles[0];
$fileName = basename($filePath);

// Devolver la URL temporal para que el cliente proceda a la descarga real
echo json_encode([
    'success' => true,
    'file' => 'download_file.php?file=' . urlencode($fileName)
]);
exit;
