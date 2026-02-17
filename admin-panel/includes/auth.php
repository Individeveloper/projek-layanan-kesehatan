<?php
// Authentication check for admin pages
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin or doctor (any specialization)
if ($_SESSION['role'] !== 'admin' && strpos($_SESSION['role'], 'doctor-') !== 0) {
    header('Location: login.php');
    exit;
}
?>
