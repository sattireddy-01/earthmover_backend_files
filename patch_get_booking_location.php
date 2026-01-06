<?php
$file = 'C:\\xampp\\htdocs\\Earth_mover\\get_user_bookings.php';
$content = file_get_contents($file);

// Add location to SELECT list if not already there, but we are replacing the loop anyway
// The issue is in the loop where it hardcodes 'Not specified'

$newContent = str_replace(
    "'location' => 'Not specified',",
    "'location' => \$row['location'] ? \$row['location'] : 'Not specified',",
    $content
);

file_put_contents($file, $newContent);
echo "Patched get_user_bookings.php successfully";
?>
