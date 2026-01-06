
    // Handle location
    if (isset($data['location'])) {
        $updateFields[] = "location = ?";
        $updateValues[] = empty($data['location']) ? null : $data['location'];
        $types .= 's';
        error_log("Including location in update: " . ($data['location'] ?: 'NULL'));
    }
