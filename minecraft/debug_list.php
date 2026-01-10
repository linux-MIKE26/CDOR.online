<?php
// Dump what PHP sees in servers.json
$file = __DIR__ . '/servers.json';
echo "Reading: $file\n";
if (file_exists($file)) {
    echo "File exists.\n";
    $content = file_get_contents($file);
    echo "Raw content length: " . strlen($content) . "\n";
    $servers = json_decode($content, true);
    print_r($servers);
} else {
    echo "File not found.\n";
}
?>
