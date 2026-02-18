<?php
// Authentication check for admin pages
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only admin can access admin panel
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
