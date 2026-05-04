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
//echo "Connected successfully";



function sendNotification($title, $body, $topic) {
    $projectId = 'indicash-aff24'; 

    // Get the access token
    $accessToken = getAccessTokens();

    if ($accessToken) {
        $notification = array(
            'title' => $title,
            'body'  => $body,
        );

        $message = array(
            'notification' => $notification,
            'topic' => $topic, // Use the provided topic
        );

        $fields = array(
            'message' => $message,
        );

        $headers = array(
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            echo "cURL Error: $error";
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = json_decode($result, true);

            if ($httpCode == 200) {
                echo "Notification sent successfully";
            } else {
                echo "Firebase Error: " . $response['error']['message'];
                // print_r($response);
            }
        }

        curl_close($ch);
    } else {
        echo "Failed to retrieve access token";
    }
}



function getAccessTokens() {
    $keyFilePath = 'dmboos.json'; // Path to your service account JSON file
    $json = file_get_contents($keyFilePath);
    $key = json_decode($json, true);

    $jwt = createJWTs($key);

    $url = 'https://oauth2.googleapis.com/token';
    $postData = [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        echo "cURL Error: $error";
        return null;
    }

    curl_close($ch);

    $response = json_decode($result, true);

    if (isset($response['access_token'])) {
        return $response['access_token'];
    } else {
        echo "Error fetching access token: " . $response['error'];
        // print_r($response);
        return null;
    }
}

function createJWTs($key) {
    $header = [
        'alg' => 'RS256',
        'typ' => 'JWT'
    ];

    $payload = [
        'iss' => $key['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600, // 1 hour expiration
        'iat' => time()
    ];

    $headerEncoded = base64url_encode(json_encode($header));
    $payloadEncoded = base64url_encode(json_encode($payload));
    $signatureInput = $headerEncoded . '.' . $payloadEncoded;

    // $privateKey = $key['private_key'];
    $privateKey = str_replace("\\n", "\n", $key['private_key']);
    $privateKey = openssl_pkey_get_private($privateKey);
    
    openssl_sign($signatureInput, $signature, $privateKey, 'sha256');
    $signatureEncoded = base64url_encode($signature);

    return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function getRate($market,$game){


$servername = "localhost";
$username = "indicashm_db";
$password = "AEoFuow6S&FY";
$dbname = "indicashm_db";

  // Create connection
  $con = mysqli_connect($servername, $username, $password,$dbname);

  $check_man = mysqli_query($con,"select rate from market_rates where market='$market' AND game='$game'");
  if(mysqli_num_rows($check_man)>0){
   $get_man = mysqli_fetch_array($check_man); 
    return $get_man['rate'];
  } else {
    $get_rate = mysqli_fetch_array(mysqli_query($con,"select * from rate"));
    return $get_rate[$game];
  }
}


function log_action($remark) {

$servername = "localhost";
$username = "apluscrm_mtkkdb";
$password = "&RNDrt3LA3sF";
$dbname = "apluscrm_mtkdb";


  // Create connection
  $con = mysqli_connect($servername, $username, $password,$dbname);
        $ip_address = $_SERVER['REMOTE_ADDR'];  
        $user_agent = $_SERVER['HTTP_USER_AGENT'];  
        $id=$_SESSION['userID'];
        $insert_stmt = $con->prepare("INSERT INTO `login_logs` (`user_email`, `ip_address`, `user_agent`, `remark`) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("ssss", $id, $ip_address, $user_agent, $remark);
        if ($insert_stmt->execute()) {
    } else {
        echo "Error logging action: " . $insert_stmt->error;
    }
    $insert_stmt->close();
}
?>