<?php
// Patch update_user_profile.php
$file = 'C:/xampp/htdocs/Earth_mover/api/user/update_user_profile.php';
$content = file_get_contents($file);
if ($content === false) die("Failed to read $file");

$snippet = "\n    // Handle location\n    if (isset(\$data['location'])) {\n        \$updateFields[] = \"location = ?\";\n        \$updateValues[] = empty(\$data['location']) ? null : \$data['location'];\n        \$types .= 's';\n        error_log(\"Including location in update: \" . (\$data['location'] ?: 'NULL'));\n    }\n\n    // Handle address";

if (strpos($content, '// Handle location') === false) {
    echo "Patching update_user_profile.php...\n";
    $content = str_replace('// Handle address', $snippet, $content);
    file_put_contents($file, $content);
} else {
    echo "update_user_profile.php already patched.\n";
}

// Patch get_operator_bookings.php
$file = 'C:/xampp/htdocs/Earth_mover/api/operator/get_operator_bookings.php';
$content = file_get_contents($file);
if ($content === false) die("Failed to read $file");

// Add location to SELECT
if (strpos($content, 'u.location as user_location') === false) {
    echo "Patching get_operator_bookings.php (SQL)...\n";
    $content = str_replace('u.address as user_address,', "u.address as user_address,\n            u.location as user_location,", $content);
}

// Add location to response
if (strpos($content, "'user_location' => \$row['user_location']") === false) {
    echo "Patching get_operator_bookings.php (Response)...\n";
    $content = str_replace("'user_address' => \$row['user_address'],", "'user_address' => \$row['user_address'],\n            'user_location' => \$row['user_location'],", $content);
    file_put_contents($file, $content);
} else {
    echo "get_operator_bookings.php already patched.\n";
}

// Patch get_user_profile.php
$file = 'C:/xampp/htdocs/Earth_mover/api/user/get_user_profile.php';
$content = file_get_contents($file);
if ($content === false) die("Failed to read $file");

// Add location to SELECT
if (strpos($content, 'location,') === false) {
    echo "Patching get_user_profile.php (SQL)...\n";
    $content = str_replace('address,', "address,\n                location,", $content);
}

// Add location to response
if (strpos($content, "'location' => \$user['location'] ?? null") === false) {
    echo "Patching get_user_profile.php (Response)...\n";
    $content = str_replace("'address' => \$user['address'] ?? null,", "'address' => \$user['address'] ?? null,\n            'location' => \$user['location'] ?? null,", $content);
    file_put_contents($file, $content);
} else {
    echo "get_user_profile.php already patched.\n";
}

echo "All files patched successfully.\n";
?>
