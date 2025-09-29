<?php
/**
 * Storage Cleanup and Setup Script
 * Run this script to clean up storage and recreate links
 */

echo "=== Storage Cleanup and Setup ===\n";

// Get the base path
$basePath = __DIR__;
$storagePath = $basePath . '/storage/app/public/';
$publicStoragePath = $basePath . '/public/storage';

echo "Base path: $basePath\n";
echo "Storage path: $storagePath\n";
echo "Public storage path: $publicStoragePath\n\n";

// 1. Remove existing storage link if it exists
if (is_link($publicStoragePath) || is_dir($publicStoragePath)) {
    echo "Removing existing storage link...\n";
    if (is_link($publicStoragePath)) {
        unlink($publicStoragePath);
        echo "✓ Removed symbolic link\n";
    } else {
        rmdir($publicStoragePath);
        echo "✓ Removed directory\n";
    }
}

// 2. Create storage directory if it doesn't exist
if (!is_dir($storagePath)) {
    echo "Creating storage directory...\n";
    mkdir($storagePath, 0755, true);
    echo "✓ Created storage directory\n";
}

// 3. Create storage link
echo "Creating storage link...\n";
if (symlink($storagePath, $publicStoragePath)) {
    echo "✓ Storage link created successfully\n";
} else {
    echo "✗ Failed to create storage link\n";
}

// 4. Set permissions
echo "Setting permissions...\n";
chmod($storagePath, 0755);
chmod($publicStoragePath, 0755);
echo "✓ Permissions set\n";

// 5. List current files
echo "\n=== Current Files in Storage ===\n";
if (is_dir($storagePath)) {
    $files = scandir($storagePath);
    $fileCount = 0;
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $storagePath . $file;
            $size = is_file($filePath) ? filesize($filePath) : 0;
            echo "- $file (" . number_format($size) . " bytes)\n";
            $fileCount++;
        }
    }
    echo "Total files: $fileCount\n";
} else {
    echo "Storage directory not found!\n";
}

// 6. Test the link
echo "\n=== Testing Storage Link ===\n";
if (is_link($publicStoragePath)) {
    $target = readlink($publicStoragePath);
    echo "✓ Link exists and points to: $target\n";
} else {
    echo "✗ Storage link not found\n";
}

echo "\n=== Setup Complete ===\n";
echo "You can now access files via:\n";
echo "- Storage link: " . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'https://api1.amamaza.rw') . "/storage/filename\n";
echo "- API route: " . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'https://api1.amamaza.rw') . "/api/images/filename\n";
echo "- API route: " . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'https://api1.amamaza.rw') . "/api/documents/filename\n";
?>
