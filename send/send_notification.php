<?php
/*
1. If response has errors like UNREGISTERED or INVALID_ARGUMENT, delete the corresponding device token.
2. Update tokens regularly.
*/

require_once __DIR__."/../vendor/autoload.php";

function getGoogleAccessToken() {
    $credentialsFilePath = 'firebase-service.json'; 
    $client = new \Google_Client();
    $client->setAuthConfig($credentialsFilePath);
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    $client->refreshTokenWithAssertion();
    $token = $client->getAccessToken();
    return $token['access_token'];
}

function sendMessage($data = []) {
    $project_id = json_decode(file_get_contents("firebase-service.json"), true)['project_id'];
    $apiurl = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send'; 
    $headers = [
        'Authorization: Bearer ' . getGoogleAccessToken(),
        'Content-Type: application/json'
    ];

    $notification_tray = [
        'title' => $data['title'],
        'body' => $data['body'],
    ];

    if (!empty($data['image_link'])) {
        $notification_tray['image'] = $data['image_link'];
    }

    $message = [
        'message' => [
            'notification' => $notification_tray,
            'token' => $data['token']
        ],
    ];

    if (!empty($data['click_action'])) {
        $message['message']['data']['url'] = $data['click_action'];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiurl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }

    curl_close($ch);
    return json_decode($result, true);
}

function handleFCMResponse($response, $deviceToken) {
    if (isset($response['error'])) {
        if (in_array($response['error']['code'], [404, 400])) {
            removeDeviceToken($deviceToken);
            return ['tokenStatus' => 'removed', "response"=>$response, 'token' => $deviceToken]; 
        }
    }
    return ["response"=>$response, 'tokenStatus' => 'ok', 'token' => $deviceToken]; 
}

function removeDeviceToken($deviceToken) {
    $tokens = json_decode(file_get_contents(__DIR__.'/../tokens.json'), true);
    foreach($tokens as $key=>$token){
        if($token['token']==$deviceToken){
            unset($tokens[$key]);
            break;
        }
    }
    file_put_contents(__DIR__.'/../tokens.json', json_encode($tokens, JSON_PRETTY_PRINT)); 
}

$data = json_decode(file_get_contents('php://input'), true);
$device_token = $data['device_tokens'] ?? null;
$title = $data['title'] ?? '';
$body = $data['body'] ?? '';

if (!empty($device_token)) {
    $responseArr = [];
    foreach ($device_token as $d) {
        $data['token'] = $d;
        $response = sendMessage($data);
        $responseStatus = handleFCMResponse($response, $d); 
        array_push($responseArr, $responseStatus); 
    }
    echo json_encode($responseArr);
} else {
    echo json_encode(['error' => 'Device token is required.']);
}
