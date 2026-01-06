<?php
$file = 'C:/xampp/htdocs/Earth_mover/api/user/update_user_profile.php';
$content = file_get_contents($file);
if ($content === false) die("Failed to read $file");

$snippet = "\n    // Handle latitude\n    if (isset(\$data['latitude'])) {\n        \$updateFields[] = \"latitude = ?\";\n        \$updateValues[] = empty(\$data['latitude']) ? null : \$data['latitude'];\n        \$types .= 'd';\n    }\n\n    // Handle longitude\n    if (isset(\$data['longitude'])) {\n        \$updateFields[] = \"longitude = ?\";\n        \$updateValues[] = empty(\$data['longitude']) ? null : \$data['longitude'];\n        \$types .= 'd';\n    }\n\n    // Handle location";

if (strpos($content, '// Handle latitude') === false) {
    echo "Patching update_user_profile.php (lat/long)...\n";
    $content = str_replace('// Handle location', $snippet, $content);
    file_put_contents($file, $content);
    echo "Patched successfully.\n";
} else {
    echo "update_user_profile.php already patched.\n";
}
?>
