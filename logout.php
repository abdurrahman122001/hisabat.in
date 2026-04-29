<?php
// Configure session cookie path for subdirectory installation
$cookiePath = dirname($_SERVER['PHP_SELF']) . '/';
if ($cookiePath === '//') $cookiePath = '/';
session_set_cookie_params(['path' => $cookiePath]);
session_start(); // Start the session

@include(__DIR__ . '/config.php');

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $role = isset($_SESSION['role']) ? (string)$_SESSION['role'] : '';
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($role === 'user' && $uid > 0 && isset($con) && ($con instanceof mysqli) && !$con->connect_errno) {
        @mysqli_query($con, "UPDATE users SET is_online=0, last_seen=NOW() WHERE id=$uid");
    }
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page with dynamic path
$redirectPath = dirname($_SERVER['PHP_SELF']) . '/login.php';
header("Location: " . $redirectPath);
exit;
?>
