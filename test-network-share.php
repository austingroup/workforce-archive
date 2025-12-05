<?php
/**
 * Network Share Connectivity Test
 * Tests connection to Windows UNC path and file operations
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Network Share Connectivity Test</h1>";
echo "<pre>";

// Configuration
$basePath = '\\\\gazman.com.au\\AustinGroup\\Archive\\Company\\GAZMAN\\VM\\Workforce\\gazman-exported-gcs-files';
$testImagePath = $basePath . '\\image\\post';

echo "=== Configuration ===\n";
echo "Base Path: {$basePath}\n";
echo "Test Image Path: {$testImagePath}\n\n";

// Test 1: Check if base path exists
echo "=== Test 1: Base Path Exists ===\n";
if (file_exists($basePath)) {
    echo "✓ Base path exists and is accessible\n";
} else {
    echo "✗ Base path does NOT exist or is not accessible\n";
    echo "Error: " . error_get_last()['message'] ?? 'Unknown error' . "\n";
}
echo "\n";

// Test 2: Check if base path is readable
echo "=== Test 2: Base Path Readable ===\n";
if (is_readable($basePath)) {
    echo "✓ Base path is readable\n";
} else {
    echo "✗ Base path is NOT readable\n";
    echo "Error: " . error_get_last()['message'] ?? 'Unknown error' . "\n";
}
echo "\n";

// Test 3: Check if image/post directory exists
echo "=== Test 3: Image/Post Directory Exists ===\n";
if (file_exists($testImagePath)) {
    echo "✓ Image/post directory exists\n";
} else {
    echo "✗ Image/post directory does NOT exist\n";
    echo "Error: " . error_get_last()['message'] ?? 'Unknown error' . "\n";
}
echo "\n";

// Test 4: Try to list directories in image/post
echo "=== Test 4: List Directories ===\n";
if (is_dir($testImagePath)) {
    echo "Attempting to read directory contents...\n";
    $dirs = @scandir($testImagePath);
    if ($dirs !== false) {
        $count = count($dirs) - 2; // Exclude . and ..
        echo "✓ Successfully read directory\n";
        echo "Found {$count} items\n";
        echo "First 10 items:\n";
        $shown = 0;
        foreach ($dirs as $dir) {
            if ($dir !== '.' && $dir !== '..' && $shown < 10) {
                echo "  - {$dir}\n";
                $shown++;
            }
        }
    } else {
        echo "✗ Failed to read directory\n";
        $error = error_get_last();
        echo "Error: " . ($error['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "✗ Path is not a directory or not accessible\n";
}
echo "\n";

// Test 5: Test with a known folder ID
echo "=== Test 5: Test Known Folder ===\n";
$knownFolderId = '14f2d900-2ab9-11ee-8940-9fbbe4efc661'; // From our database check
$knownFileName = 'ios.jpeg';
$testFilePath = $testImagePath . '\\' . $knownFolderId . '\\' . $knownFileName;
echo "Test file path: {$testFilePath}\n";

if (file_exists($testFilePath)) {
    echo "✓ Test file exists\n";
    
    if (is_readable($testFilePath)) {
        echo "✓ Test file is readable\n";
        
        $size = @filesize($testFilePath);
        if ($size !== false) {
            echo "✓ File size: " . number_format($size) . " bytes (" . round($size/1024, 2) . " KB)\n";
        } else {
            echo "✗ Could not get file size\n";
        }
        
        if (function_exists('mime_content_type')) {
            $mimeType = @mime_content_type($testFilePath);
            if ($mimeType) {
                echo "✓ MIME type: {$mimeType}\n";
            }
        } else {
            // Fallback: detect from extension
            $ext = strtolower(pathinfo($testFilePath, PATHINFO_EXTENSION));
            $mimeTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
            echo "✓ MIME type (from extension): " . ($mimeTypes[$ext] ?? 'unknown') . "\n";
        }
    } else {
        echo "✗ Test file is NOT readable\n";
    }
} else {
    echo "✗ Test file does NOT exist\n";
    $error = error_get_last();
    echo "Error: " . ($error['message'] ?? 'Unknown error') . "\n";
}
echo "\n";

// Test 6: PHP Configuration
echo "=== Test 6: PHP Configuration ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Operating System: " . PHP_OS . "\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'On' : 'Off') . "\n";
echo "open_basedir: " . (ini_get('open_basedir') ?: 'Not set') . "\n";
echo "Current working directory: " . getcwd() . "\n";
echo "\n";

// Test 7: Alternative path formats
echo "=== Test 7: Alternative Path Formats ===\n";
$altPaths = [
    'Forward slashes' => str_replace('\\', '/', $testImagePath),
    'Mixed slashes' => '//gazman.com.au/AustinGroup/Archive/Company/GAZMAN/VM/Workforce/gazman-exported-gcs-files/image/post',
    'file:// protocol' => 'file://' . $testImagePath,
];

foreach ($altPaths as $name => $path) {
    echo "{$name}: ";
    if (@file_exists($path)) {
        echo "✓ Accessible\n";
    } else {
        echo "✗ Not accessible\n";
    }
}
echo "\n";

// Test 8: Network connectivity
echo "=== Test 8: Network Connectivity ===\n";
$host = 'gazman.com.au';
echo "Testing DNS resolution for {$host}...\n";
$ip = @gethostbyname($host);
if ($ip !== $host) {
    echo "✓ DNS resolved to: {$ip}\n";
} else {
    echo "✗ Could not resolve {$host}\n";
}
echo "\n";

// Test 9: Permission test
echo "=== Test 9: Current User Context ===\n";
if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
    $processUser = posix_getpwuid(posix_geteuid());
    echo "PHP running as user: " . ($processUser['name'] ?? 'Unknown') . "\n";
} else {
    echo "POSIX functions not available (running on Windows or disabled)\n";
}
if (function_exists('get_current_user')) {
    echo "Current user: " . get_current_user() . "\n";
}
echo "\n";

echo "=== Test Complete ===\n";
echo "If you see errors about network paths, you may need to:\n";
echo "1. Ensure the web server has permission to access the network share\n";
echo "2. Configure network credentials in your web server\n";
echo "3. Map the network drive or use authentication\n";
echo "4. Check Windows SMB/CIFS permissions\n";

echo "</pre>";
