<?php
/**
 * Direct Storage Fix Script
 * Access this file directly in your browser
 */

header('Content-Type: application/json');

// Get the base path
$basePath = dirname(__DIR__);
$storagePath = $basePath . '/storage/app/public/';
$publicStoragePath = $basePath . '/public/storage';

$results = [];
$success = true;

// 1. Remove existing storage link if it exists
if (is_link($publicStoragePath) || is_dir($publicStoragePath)) {
    if (is_link($publicStoragePath)) {
        unlink($publicStoragePath);
        $results[] = "✓ Removed existing symbolic link";
    } else {
        rmdir($publicStoragePath);
        $results[] = "✓ Removed existing directory";
    }
} else {
    $results[] = "✓ No existing storage link found";
}

// 2. Create storage directory if it doesn't exist
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0755, true);
    $results[] = "✓ Created storage directory";
} else {
    $results[] = "✓ Storage directory already exists";
}

// 3. Create storage link
if (symlink($storagePath, $publicStoragePath)) {
    $results[] = "✓ Storage link created successfully";
} else {
    $results[] = "✗ Failed to create storage link";
    $success = false;
}

// 4. Set permissions
chmod($storagePath, 0755);
if (is_link($publicStoragePath)) {
    chmod($publicStoragePath, 0755);
}
$results[] = "✓ Permissions set";

// 5. List current files
$files = [];
if (is_dir($storagePath)) {
    $fileList = scandir($storagePath);
    foreach ($fileList as $file) {
        if ($file != '.' && $file != '..' && !is_dir($storagePath . $file)) {
            $files[] = [
                'name' => $file,
                'size' => filesize($storagePath . $file),
                'modified' => date('Y-m-d H:i:s', filemtime($storagePath . $file))
            ];
        }
    }
}

// 6. Test the link
$linkTest = is_link($publicStoragePath) ? "✓ Link exists" : "✗ Storage link not found";
$results[] = $linkTest;

// Final response
$response = [
    'success' => $success,
    'message' => $success ? 'Storage setup completed successfully!' : 'Storage setup completed with errors',
    'results' => $results,
    'files' => $files,
    'file_count' => count($files),
    'storage_path' => $storagePath,
    'public_storage_path' => $publicStoragePath,
    'base_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'],
    'test_urls' => [
        'storage_link' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/storage/',
        'api_images' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/images/',
        'api_documents' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/documents/',
        'list_files' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/files',
        'storage_info' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/storage-info'
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
