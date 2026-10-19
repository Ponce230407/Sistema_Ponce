<?php
session_start();
$_SESSION = [];
session_destroy();

header('Location: ../LOGIN/login.php');
exit;
