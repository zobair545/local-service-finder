<?php
// logout.php
session_start();
session_destroy();
header('Location: /project/login.php');
exit;
?>
