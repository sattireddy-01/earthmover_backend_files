<?php
require_once 'C:/xampp/htdocs/Earth_mover/database.php';

$machineId = 10;
$machineType = '';

echo "Testing Database Connection...\n";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
echo "Connected.\n";

echo "Testing specific query...\n";
try {
    $query = "SELECT o.* FROM operators o 
              LEFT JOIN machines m ON o.operator_id = m.operator_id 
              WHERE (m.machine_id = ? OR m.equipment_type = ?) 
              AND o.status = 'available' 
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error . "\n");
    }
    $stmt->bind_param("is", $machineId, $machineType);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "Query 1 Result rows: " . $result->num_rows . "\n";
    if ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Exception in Query 1: " . $e->getMessage() . "\n";
}

echo "\nTesting fallback query...\n";
try {
    $queryFallback = "SELECT * FROM operators WHERE status = 'available' LIMIT 1";
    $resultFallback = $conn->query($queryFallback);
    if (!$resultFallback) {
         die("Fallback query failed: " . $conn->error . "\n");
    }
    echo "Fallback Result rows: " . $resultFallback->num_rows . "\n";
    if ($row = $resultFallback->fetch_assoc()) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Exception in Fallback: " . $e->getMessage() . "\n";
}
?>
