<?php
require_once 'init.php';

// Clear session securely
$_SESSION = [];
session_destroy();

// Regenerate session ID to prevent fixation
session_start();
session_regenerate_id(true);

header("Location: login.php");
exit;
