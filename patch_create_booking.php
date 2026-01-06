<?php
$file = 'C:\\xampp\\htdocs\\Earth_mover\\api\\booking\\create_booking.php';
$content = file_get_contents($file);

// 1. Get location from input
$search1 = '$totalAmount = isset($data[\'total_amount\']) ? floatval($data[\'total_amount\']) : 0.0;';
$replace1 = '$totalAmount = isset($data[\'total_amount\']) ? floatval($data[\'total_amount\']) : 0.0;
    $location = isset($data[\'location\']) ? $data[\'location\'] : \'Not specified\';';

// 2. Add location to INSERT statement
$search2 = '        amount,
        status
    ) VALUES (?, ?, ?, ?, ?, \'PENDING\')");';
$replace2 = '        amount,
        status,
        location
    ) VALUES (?, ?, ?, ?, ?, \'PENDING\', ?)");';

// 3. Bind the new parameter (add 's' to types string and $location var)
$search3 = '$stmt->bind_param("iiiid", 
        $userId,
        $operatorId,
        $machineId,
        $hours,
        $totalAmount
    );';
$replace3 = '$stmt->bind_param("iiiids", 
        $userId,
        $operatorId,
        $machineId,
        $hours,
        $totalAmount,
        $location
    );';

$newContent = str_replace($search1, $replace1, $content);
$newContent = str_replace($search2, $replace2, $newContent);
$newContent = str_replace($search3, $replace3, $newContent);

file_put_contents($file, $newContent);
echo "Patched create_booking.php successfully";
?>
