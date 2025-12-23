<?php
// Create test_upload.zip from public/theme_examples/test_upload
// Usage: php scripts/create_test_upload_zip.php

$root = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
$source = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'theme_examples' . DIRECTORY_SEPARATOR . 'theme';
$dest = $root . DIRECTORY_SEPARATOR . 'theme.zip';

if (!is_dir($source)) {
    fwrite(STDERR, "Source folder not found: $source\n");
    exit(1);
}

// Remove existing zip if present
if (file_exists($dest)) {
    unlink($dest);
}

// Helper to add a directory recursively with ZipArchive
function addFolderToZip($dir, ZipArchive $zip, $basePathLen) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        if (!$filePath) continue;
        $localPath = substr($filePath, $basePathLen + 1);
        $zip->addFile($filePath, $localPath);
    }
}

// Try using ZipArchive first
if (class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    if ($zip->open($dest, ZipArchive::CREATE) !== true) {
        fwrite(STDERR, "Cannot create zip file at $dest\n");
        exit(1);
    }
    addFolderToZip($source, $zip, strlen($source));
    $zip->close();
    echo "Created zip (ZipArchive): $dest\n";
    exit(0);
}

// Fallbacks
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Use PowerShell Compress-Archive
    $ps = 'powershell -NoProfile -Command ';
    // Ensure paths are Windows style for PowerShell
    $sourcePS = str_replace("/", "\\\\", $source) . '\\\\*';
    $destPS = str_replace("/", "\\\\", $dest);
    $cmd = $ps . '"Compress-Archive -Force -Path ' . escapeshellarg($sourcePS) . ' -DestinationPath ' . escapeshellarg($destPS) . '"';
    exec($cmd . ' 2>&1', $output, $rc);
    if ($rc === 0) {
        echo "Created zip (PowerShell): $dest\n";
        exit(0);
    }
    fwrite(STDERR, "PowerShell Compress-Archive failed:\n" . implode("\n", $output) . "\n");
    exit(2);
} else {
    // Attempt to use system zip
    $cmd = 'zip -r ' . escapeshellarg($dest) . ' .';
    $cwd = $source;
    $descriptorspec = array(
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w')
    );
    $process = proc_open($cmd, $descriptorspec, $pipes, $cwd);
    if (is_resource($process)) {
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $rc = proc_close($process);
        if ($rc === 0) {
            echo "Created zip (zip): $dest\n";
            exit(0);
        }
        fwrite(STDERR, "zip failed (rc=$rc):\n$stderr\n$stdout\n");
        exit(3);
    }
    fwrite(STDERR, "Cannot run zip command\n");
    exit(4);
}
