<?php
require_once 'init.php';
require_once 'config.php';

session_start();
$_SESSION = [];
session_destroy();

header("Location: index.php");
exit;
