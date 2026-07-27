<?php
$apiKey = "QORDHM3PLG21";
$apiSecret = "65f1cb48-0fde-4568-97a6-349c1a499f45";
$baseUrl = "https://dpbossresultapi.com";
$path = "/api/data";
$method = "GET";

// Get the current time in RFC3339 format (e.g., 2025-06-03T15:01:00Z)
$timestamp = gmdate("Y-m-d\TH:i:s\Z");

// Combine the values to create the message
$message = $apiKey . $method . $path . $timestamp;

// Create the signature using HMAC-SHA256
$signature = hash_hmac('sha256', $message, $apiSecret);

// Set up the headers
$headers = [
    "X-API-Key: $apiKey",
    "X-Timestamp: $timestamp",
    "X-Signature: $signature"
];

// Set up the cURL request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . $path);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_ENCODING, '');

// Send the request and get the response
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo '<pre>';
print_r($response);
echo '</pre>';
// Check for errors
if (curl_errno($ch)) {
    echo "Error: " . curl_error($ch);
} else {
    echo "HTTP Status: $httpCode\n";
    // echo "Response:\n$response";
}

curl_close($ch);
?>