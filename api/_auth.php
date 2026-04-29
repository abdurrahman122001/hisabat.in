<?php
// Shared auth helpers for API endpoints
// Uses existing PHP session (login.php)

// Configure session cookie path for subdirectory installation
if (session_status() === PHP_SESSION_NONE) {
    $cookiePath = dirname($_SERVER['PHP_SELF']) . '/';
    if (strpos($cookiePath, '/api/') !== false) {
        $cookiePath = dirname(dirname($_SERVER['PHP_SELF'])) . '/';
    }
    if ($cookiePath === '//') $cookiePath = '/';
    session_set_cookie_params(['path' => $cookiePath]);
    session_start();
}

function auth_user_role(): string
{
    return isset($_SESSION['role']) ? (string)$_SESSION['role'] : '';
}

function auth_is_superadmin(): bool
{
    return auth_user_role() === 'superadmin';
}

function auth_is_user(): bool
{
    return auth_user_role() === 'user';
}

function auth_user_id(): string
{
    // For normal users: numeric id in users table. For superadmin: string marker.
    if (isset($_SESSION['user_id'])) {
        return (string)$_SESSION['user_id'];
    }
    return '';
}

function auth_user_id_int(): int
{
    if (!auth_is_user()) return 0;
    $id = (int)auth_user_id();
    return $id > 0 ? $id : 0;
}

function require_login(): void
{
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
        exit;
    }
}

function require_role(array $roles): void
{
    require_login();
    $role = auth_user_role();
    if ($role === '') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Role not set']);
        exit;
    }
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Not authorized']);
        exit;
    }
}

function forbid_role(array $roles): void
{
    require_login();
    $role = auth_user_role();
    if ($role !== '' && in_array($role, $roles, true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Not authorized']);
        exit;
    }
}
