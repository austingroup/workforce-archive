<?php
/**
 * Image Proxy - Serves images from Windows network share
 * Path: \\gazman.com.au\AustinGroup\Archive\Company\GAZMAN\VM\Workforce\gazman-exported-gcs-files\image\post\{folder_id}\{filename}
 */

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get parameters
$folderId = isset($_GET['folder_id']) ? $_GET['folder_id'] : '';
$fileName = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($folderId) || empty($fileName)) {
    http_response_code(400);
    echo "Missing parameters";
    exit;
}

// Construct the full UNC path
$basePath = '\\\\gazman.com.au\\AustinGroup\\Archive\\Company\\GAZMAN\\VM\\Workforce\\gazman-exported-gcs-files';
$filePath = $basePath . '\\image\\post\\' . $folderId . '\\' . $fileName;

// Check if file exists and is readable
if (!file_exists($filePath) || !is_readable($filePath)) {
    // Serve placeholder image if file not found
    $placeholderPath = __DIR__ . '/assets/images/workforce.jpeg';
    if (file_exists($placeholderPath)) {
        header('Content-Type: image/jpeg');
        readfile($placeholderPath);
        exit;
    }
    http_response_code(404);
    echo "File not found: " . htmlspecialchars($filePath);
    exit;
}

// Get file size for Content-Length header
$fileSize = filesize($filePath);
if ($fileSize === false) {
    http_response_code(500);
    exit;
}

// Detect mime type based on extension
$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mimeTypes = array(
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'pdf' => 'application/pdf'
);

$mimeType = isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream';

// Set appropriate headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Output the file
readfile($filePath);
exit;
