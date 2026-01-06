

<?php
// index.php

header('Content-Type', 'application/json');

echo json_encode([
    'success' => true,
    'message' => 'EarthMover API is running',
    'routes'  => [
        '/api/auth/signup.php',
        '/api/auth/login.php',
        '/api/auth/otp_verify.php',
        '/api/user/profile.php',
        '/api/user/service_history.php',
        '/api/machines/categories.php',
        '/api/machines/models.php',
        '/api/machines/machine_details.php',
        '/api/booking/create_booking.php',
        '/api/booking/booking_status.php',
        '/api/booking/complete_booking.php',
        '/api/operator/availability.php',
        '/api/operator/accept_booking.php',
        '/api/operator/earnings.php',
        '/api/payment/initiate_payment.php',
        '/api/payment/payment_history.php',
        '/api/admin/dashboard.php',
        '/api/admin/verify_operator.php',
        '/api/admin/reports.php',
    ],
]);