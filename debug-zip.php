<?php
// فایل debug-zip.php در ریشه وردپرس
// دسترسی به این فایل فقط برای مدیر امکان‌پذیر است

require_once('wp-load.php');

// بررسی دسترسی
if (!current_user_can('manage_options')) {
    die('Zugriff verweigert');
}

// مسیر به فایل ZIP را تنظیم کنید
$zip_path = $_GET['zip'] ?? '';

if (empty($zip_path) || !file_exists($zip_path)) {
    $upload_dir = wp_upload_dir();
    $dir = $upload_dir['basedir'] . '/aci-temp/';
    $files = glob($dir . '*.zip');
    
    echo '<h1>Verfügbare ZIP-Dateien</h1>';
    if (empty($files)) {
        echo '<p>Keine ZIP-Dateien gefunden in: ' . $dir . '</p>';
    } else {
        echo '<ul>';
        foreach ($files as $file) {
            echo '<li><a href="?zip=' . urlencode($file) . '">' . basename($file) . '</a></li>';
        }
        echo '</ul>';
    }
    die();
}

// بررسی محتوای ZIP
$zip = new ZipArchive();
if ($zip->open($zip_path) !== true) {
    die('Fehler beim Öffnen der ZIP-Datei');
}

echo '<h1>ZIP-Datei: ' . basename($zip_path) . '</h1>';
echo '<p>Anzahl der Dateien: ' . $zip->numFiles . '</p>';

echo '<h2>Dateiliste:</h2>';
echo '<ul>';
for ($i = 0; $i < $zip->numFiles; $i++) {
    $filename = $zip->getNameIndex($i);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    echo '<li>' . $filename . ' (' . $ext . ')</li>';
}
echo '</ul>';

echo '<h2>CSV-Dateien:</h2>';
$csv_files = array();
for ($i = 0; $i < $zip->numFiles; $i++) {
    $filename = $zip->getNameIndex($i);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext === 'csv') {
        $csv_files[] = $filename;
    }
}

if (empty($csv_files)) {
    echo '<p>Keine CSV-Dateien gefunden</p>';
} else {
    echo '<ul>';
    foreach ($csv_files as $file) {
        echo '<li>' . $file . '</li>';
    }
    echo '</ul>';
}

$zip->close();