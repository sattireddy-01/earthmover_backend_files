

<?php

require_once __DIR__ . '/response.php';

/**
 * Simple user auth:
 * Expect header: X-User-Id: <user_id>
 */
function require_user()
{
    $userId = isset($_SERVER['HTTP_X_USER_ID']) ? (int) $_SERVER['HTTP_X_USER_ID'] : 0;

    if (!$userId) {
        send_response(false, 'Unauthorized: missing X-User-Id header', null, 401);
    }

    return $userId;
}

/**
 * Simple admin auth:
 * Expect header: X-Admin-Id: <admin_id>
 */
function require_admin()
{
    $adminId = isset($_SERVER['HTTP_X_ADMIN_ID']) ? (int) $_SERVER['HTTP_X_ADMIN_ID'] : 0;

    if (!$adminId) {
        send_response(false, 'Unauthorized: missing X-Admin-Id header', null, 401);
    }

    return $adminId;
}