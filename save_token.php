<?php
date_default_timezone_set('Asia/Kolkata');
$tokenFile = 'tokens.json';
$data = file_get_contents('php://input');
$decodedData = json_decode($data, true);

if (isset($decodedData['token']) && isset($decodedData['device_name']) && isset($decodedData['device_type']) && isset($decodedData['page_url'])) {
    
    $ipAddress = getUserIpAddr();
    
    $formattedUpdatedAt = date("d-m-Y H:i:s");

    $decodedData['created_at'] = date("d-m-Y H:i:s");

    $tokenData = array(
        'token' => $decodedData['token'],
        'device_name' => $decodedData['device_name'], 
        'device_type' => $decodedData['device_type'], 
        'page_url' => $decodedData['page_url'], 
        'ip_address' => $ipAddress, // Add the IP address
        'created_at' => $decodedData['created_at'],
        'updated_at' => $formattedUpdatedAt // Human-readable format
    );
    
    $user_data  = getUserData($ipAddress);
    if(!empty($user_data)){
        $tokenData = array_merge($tokenData, $user_data);   
    }
    if(file_exists($tokenFile)) {
        $currentData = json_decode(file_get_contents($tokenFile), true);
        if (!$currentData) {
            $currentData = [];
        }
    } else {
        $currentData = [];
    }

    $tokenExists = false;
    foreach ($currentData as &$entry) {
        if ($entry['token'] === $decodedData['token']) {
            $entry['device_name'] = $decodedData['device_name'];
            $entry['device_type'] = $decodedData['device_type'];
            $entry['ip_address'] = $ipAddress; 
            $entry['page_url'] = $decodedData['page_url'];
            $entry['updated_at'] = $formattedUpdatedAt; // Update with formatted timestamp
            $tokenExists = true;
            break;
        }
    }

    if (!$tokenExists) {
        $currentData[] = $tokenData;
    }
    if (file_put_contents($tokenFile, json_encode($currentData, JSON_PRETTY_PRINT))) {
        echo json_encode(array('status' => 'success', 'message' => 'Token saved or updated successfully.'));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Unable to save or update token.'));
    }

} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid data.'));
}

function getUserIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}


function getUserData($ip_address){
    $api_url = "https://ipinfo.io/{$ip_address}/json";
    $response = file_get_contents($api_url);
    $user_details = json_decode($response, true);
    return $user_details;
}
?>
