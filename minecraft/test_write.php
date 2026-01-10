<?php
$dataFile = 'servers.json';
$servers = json_decode(file_get_contents($dataFile), true);
$servers[0]['name'] = 'ANTIGRAVITY TEST';
file_put_contents($dataFile, json_encode($servers, JSON_PRETTY_PRINT));
echo "DONE\n";
?>
