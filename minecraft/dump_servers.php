<?php
$dataFile = __DIR__ . '/servers.json';
$content = file_get_contents($dataFile);
echo "RAW CONTENT:\n$content\n";
$servers = json_decode($content, true);
echo "DECODED:\n";
print_r($servers);
?>
