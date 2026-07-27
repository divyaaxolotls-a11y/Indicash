<?php

// API credentials and endpoint
$username = '9888195353';
$api_token = '87VZa1uH8oweNJnU';
$market_name = 'MILAN MORNING';
$date = '2024-09-11';
$api_url = 'https://matkawebhook.matka-api.online/market-data';

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: application/json' // Optional: Specify the response format you expect
));

// Define POST data as JSON
$postData = json_encode(array(
    'username' => $username,
    'api_token' => $api_token,
    'market_name' => $market_name,
    'date' => $date
));

// Set cURL POST data
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch) . "\n";
    echo 'cURL Response: ' . $response . "\n";
} else {
    // Debug: Output HTTP response code and headers
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseHeaders = curl_getinfo($ch);
    
    echo 'HTTP Response Code: ' . $httpCode . "\n";
    echo 'Response Headers: ' . print_r($responseHeaders, true) . "\n";
    echo 'Response Body: ' . $response . "\n";
    
    // Decode JSON response
    $responseData = json_decode($response, true);

    // Check for valid response
    if (isset($responseData['status']) && $responseData['status'] === true) {
        echo 'Success: ' . $responseData['message'] . "\n";
        
        // Print today's result
        if (isset($responseData['today_result'])) {
            echo "Today's Result:\n";
            foreach ($responseData['today_result'] as $result) {
                echo "Market Name: " . $result['market_name'] . "\n";
                echo "Open: " . $result['aankdo_open'] . "\n";
                echo "Close: " . $result['aankdo_close'] . "\n";
                echo "Figure Open: " . $result['figure_open'] . "\n";
                echo "Figure Close: " . $result['figure_close'] . "\n";
                echo "Jodi: " . $result['jodi'] . "\n";
                echo "-----------------------------\n";
            }
        }
        
        // Print old results
        if (isset($responseData['old_result'])) {
            echo "Old Results:\n";
            foreach ($responseData['old_result'] as $result) {
                echo "Market Name: " . $result['market_name'] . "\n";
                echo "Open: " . $result['aankdo_open'] . "\n";
                echo "Close: " . $result['aankdo_close'] . "\n";
                echo "Figure Open: " . $result['figure_open'] . "\n";
                echo "Figure Close: " . $result['figure_close'] . "\n";
                echo "Jodi: " . $result['jodi'] . "\n";
                echo "-----------------------------\n";
            }
        }
    } else {
        echo 'Failed: ' . (isset($responseData['message']) ? $responseData['message'] : 'Unknown error');
    }
}

// Close cURL session
curl_close($ch);

?>
