<?php
$pid = $argv[1] ?? 0;
echo "Checking PID $pid\n";
echo "file_exists: " . (file_exists("/proc/$pid") ? "YES" : "NO") . "\n";
echo "is_dir: " . (is_dir("/proc/$pid") ? "YES" : "NO") . "\n";
?>
