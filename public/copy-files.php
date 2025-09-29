<?php
/**
 * Copy Files to Public Directory
 * Alternative solution when symbolic links don't work
 */

header('Content-Type: application/json');

$basePath = dirname(__DIR__);
$storagePath = $basePath . '/storage/app/public/';
$publicStoragePath = $basePath . '/public/storage/';

$results = [];
$success = true;

// 1. Create public storage directory
if (!is_dir($publicStoragePath)) {
    mkdir($publicStoragePath, 0755, true);
    $results[] = "✓ Created public storage directory";
} else {
    $results[] = "✓ Public storage directory already exists";
}

// 2. Copy files from storage to public
$files = [];
if (is_dir($storagePath)) {
    $fileList = scandir($storagePath);
    foreach ($fileList as $file) {
        if ($file != '.' && $file != '..' && !is_dir($storagePath . $file)) {
            $sourceFile = $storagePath . $file;
            $destFile = $publicStoragePath . $file;
            
            if (copy($sourceFile, $destFile)) {
                $results[] = "✓ Copied: $file";
                $files[] = [
                    'name' => $file,
                    'size' => filesize($destFile),
                    'modified' => date('Y-m-d H:i:s', filemtime($destFile)),
                    'url' => 'https://api1.amamaza.rw/storage/' . $file
                ];
            } else {
                $results[] = "✗ Failed to copy: $file";
                $success = false;
            }
        }
    }
}

// 3. Set permissions
chmod($publicStoragePath, 0755);
$results[] = "✓ Permissions set";

$response = [
    'success' => $success,
    'message' => $success ? 'Files copied successfully!' : 'File copying completed with errors',
    'results' => $results,
    'files' => $files,
    'file_count' => count($files),
    'storage_path' => $storagePath,
    'public_storage_path' => $publicStoragePath,
    'test_urls' => [
        'storage_link' => 'https://api1.amamaza.rw/storage/',
        'api_images' => 'https://api1.amamaza.rw/api/images/',
        'api_documents' => 'https://api1.amamaza.rw/api/documents/'
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
