<?php

date_default_timezone_set("Asia/Kolkata");


$servername = "localhost";
$username = "apluscrm_mtkkdb";
$password = "&RNDrt3LA3sF";
$dbname = "apluscrm_mtkdb";


// Create connection
$con = mysqli_connect($servername, $username, $password,$dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
function sendNotification($title, $body, $target) {
    $projectId = 'indicash-aff24'; 
    $accessToken = getAccessTokens();

    if ($accessToken) {
        // Build the Message Payload
        $message = [
            'notification' => [
                'title' => $title,
                'body'  => $body
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'channel_id' => 'high_importance_channel'
                ]
            ],
            'data' => [ // Data payload helps trigger background handling in some apps
                'title' => $title,
                'message' => $body
            ]
        ];

        // Route to Topic or Token
        if ($target == 'all') {
            $message['topic'] = 'all';
        } else {
            $message['token'] = $target;
        }

        $fields = ['message' => $message];
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    return false;
}

// 4. JWT & AUTH HELPERS
function getAccessTokens() {
    $keyFilePath = 'dmboos.json'; 
    if (!file_exists($keyFilePath)) return null;
    
    $json = file_get_contents($keyFilePath);
    $key = json_decode($json, true);
    $jwt = createJWTs($key);

    $url = 'https://oauth2.googleapis.com/token';
    $postData = ['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($result, true);
    return $response['access_token'] ?? null;
}

function createJWTs($key) {
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $payload = [
        'iss' => $key['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600,
        'iat' => time()
    ];
    $headerEncoded = base64url_encode(json_encode($header));
    $payloadEncoded = base64url_encode(json_encode($payload));
    $signatureInput = $headerEncoded . '.' . $payloadEncoded;
    $privateKey = openssl_pkey_get_private(str_replace("\\n", "\n", $key['private_key']));
    openssl_sign($signatureInput, $signature, $privateKey, 'sha256');
    return $headerEncoded . '.' . $payloadEncoded . '.' . base64url_encode($signature);
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// 5. DATA FETCHING (Using Global $con to prevent "Connection Refused")
function getRate($market, $game) {
    global $con;
    $m = mysqli_real_escape_string($con, $market);
    $g = mysqli_real_escape_string($con, $game);

    $check_man = mysqli_query($con, "SELECT rate FROM market_rates WHERE market='$m' AND game='$g'");
    if (mysqli_num_rows($check_man) > 0) {
        $get_man = mysqli_fetch_assoc($check_man); 
        return $get_man['rate'];
    } else {
        $get_rate = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM rate LIMIT 1"));
        return $get_rate[$game] ?? 0;
    }
}

// 6. LOGGING (Consolidated into one clean function)
function log_action($remark) {
    global $con; 
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';  
    $user_agent = mysqli_real_escape_string($con, $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');  
    $id = $_SESSION['userID'] ?? 'System';
    
    $insert_stmt = $con->prepare("INSERT INTO `login_logs` (`user_email`, `ip_address`, `user_agent`, `remark`) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("ssss", $id, $ip_address, $user_agent, $remark);
    $insert_stmt->execute();
    $insert_stmt->close();
}
?>