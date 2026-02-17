<?php
session_start();
session_destroy();
header('Location: ../public-web/layout/login.php');
exit;
?>
