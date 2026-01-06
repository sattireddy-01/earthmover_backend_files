

<?php

function send_response($success, $message = '', $data = null, $status_code = 200)
{
    http_response_code($status_code);
    header('Content-Type: application/json');

    $response = [
        'success' => $success,
        'message' => $message,
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit;
}

function get_json_input()
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function require_method($method)
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        send_response(false, 'Method not allowed', null, 405);
    }
}