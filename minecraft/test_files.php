<?php
session_start();
$_SESSION['user'] = ['id' => 1];
$_GET['action'] = 'list_files';
$_GET['id'] = 'server1';

require '/home/mike/Desktop/activo/cdoronline/minecraft/api.php';
