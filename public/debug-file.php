<?php
/**
 * Debug specific file path
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$folderId = '2e9c9a26-970a-4d33-bf2a-70e4f2847eb4';
$fileName = 'ios.jpeg';

// Try different path formats
echo "<h1>Debug File Path</h1>";
echo "<pre>";

echo "=== Testing different path formats ===\n\n";

// Format 1: Double backslashes
$path1 = '\\\\gazman.com.au\\AustinGroup\\Archive\\Company\\GAZMAN\\VM\\Workforce\\gazman-exported-gcs-files\\image\\post\\' . $folderId . '\\' . $fileName;
echo "Format 1 (double backslash):\n";
echo "Path: {$path1}\n";
echo "file_exists(): " . (file_exists($path1) ? "✓ YES" : "✗ NO") . "\n\n";

// Format 2: Forward slashes
$path2 = '//gazman.com.au/AustinGroup/Archive/Company/GAZMAN/VM/Workforce/gazman-exported-gcs-files/image/post/' . $folderId . '/' . $fileName;
echo "Format 2 (forward slashes):\n";
echo "Path: {$path2}\n";
echo "file_exists(): " . (file_exists($path2) ? "✓ YES" : "✗ NO") . "\n\n";

// Format 3: Single backslash in string (will be double when output)
$path3 = "\\\gazman.com.au\AustinGroup\Archive\Company\GAZMAN\VM\Workforce\gazman-exported-gcs-files\image\post\\" . $folderId . "\\" . $fileName;
echo "Format 3 (escaped backslash):\n";
echo "Path: {$path3}\n";
echo "file_exists(): " . (file_exists($path3) ? "✓ YES" : "✗ NO") . "\n\n";

// Check current working directory and user
echo "=== Environment Info ===\n";
echo "Current working directory: " . getcwd() . "\n";
echo "Current user: " . get_current_user() . "\n";
if (function_exists('posix_getuid')) {
    echo "UID: " . posix_getuid() . "\n";
}

echo "\n=== ISSUE IDENTIFIED ===\n";
echo "The test-network-share.php worked when run from command line as 'dwadmin'\n";
echo "But the web server is running as 'workforce' user.\n";
echo "The 'workforce' user doesn't have permission to access the network share.\n\n";
echo "SOLUTION:\n";
echo "1. Change IIS Application Pool identity to 'dwadmin', OR\n";
echo "2. Grant 'workforce' user (or IIS AppPool\\workforce) read access to:\n";
echo "   \\\\gazman.com.au\\AustinGroup\\Archive\\Company\\GAZMAN\\VM\\Workforce\\gazman-exported-gcs-files\n";

echo "</pre>";
