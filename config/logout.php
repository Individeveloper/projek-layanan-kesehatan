<?php
session_start();
session_destroy();
header('Location: ../layout/login.php');
exit;
?>
