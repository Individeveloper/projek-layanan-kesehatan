<?php
// Authentication check for admin pages
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Role-based access control (default: admin only)
$allowed_roles = $allowed_roles ?? ['admin'];
$current_role = $_SESSION['role'] ?? '';

// Support legacy doctor roles such as doctor-umum, doctor-gigi, etc.
$is_doctor_role = ($current_role === 'doctor' || strpos($current_role, 'doctor-') === 0);

$has_access = in_array($current_role, $allowed_roles, true) ||
              (in_array('doctor', $allowed_roles, true) && $is_doctor_role);

if (!$has_access) {
    header('Location: ../login.php');
    exit;
}
?>
