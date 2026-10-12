<?php
require_once __DIR__ . '/Auth/role.php';
session_start();
session_destroy();
header('Location:Sign_In.php');
exit;
