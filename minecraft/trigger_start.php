<?php
session_start();
$_SESSION['user'] = ['id' => 1]; // Mock user
$_GET['action'] = 'toggle';
$_GET['id'] = 'server1';
$_GET['command'] = 'start';

require '/home/mike/Desktop/activo/cdoronline/minecraft/api.php';
