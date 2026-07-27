<?php
// 1. Enable error reporting to see hidden PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Include your config file
include('config.php');

echo "<html><body style='font-family: sans-serif; padding: 20px;'>";
echo "<h1>FCM V1 Notification Test</h1>";

// --- STEP 1: Verify Service Account File ---
$keyFilePath = 'dmboos.json';
if (!file_exists($keyFilePath)) {
    echo "<p style='color:red;'>❌ ERROR: <b>$keyFilePath</b> not found in this folder!</p>";
    exit;
} else {
    echo "<p style='color:green;'>✅ SUCCESS: <b>$keyFilePath</b> found.</p>";
}

// --- STEP 2: Test Google Authorization ---
echo "<h3>1. Testing Google Auth Token:</h3>";
$accessToken = getAccessTokens();

if ($accessToken) {
    echo "<p style='color:green;'>✅ SUCCESS: Access Token retrieved from Google.</p>";
    echo "<small style='color:#666;'>Token prefix: " . substr($accessToken, 0, 30) . "...</small>";
} else {
    echo "<p style='color:red;'>❌ ERROR: Could not get Access Token. Check if 'Firebase Cloud Messaging API (V1)' is enabled in Google Cloud Console.</p>";
    exit;
}

// --- STEP 3: Test Sending Notification ---
echo "<h3>2. Sending Test Message:</h3>";

// Change 'all' to your personal mobile number if your app subscribes to individual topics
$test_topic = "all"; 
$test_title = "Admin Test Notification";
$test_body  = "This is a test message sent at " . date('h:i:s A');

echo "<p>Target Topic: <b>$test_topic</b></p>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

// Calling the function from your config.php
sendNotification($test_title, $test_body, $test_topic);

echo "</div>";

echo "<br><hr>";
echo "<h4>Troubleshooting:</h4>";
echo "<ul>
    <li><b>If it says 'Notification sent successfully' but you got nothing:</b> The problem is in your Android/iOS app. The app is likely NOT subscribed to the topic '$test_topic'.</li>
    <li><b>If you see a Firebase Error:</b> Read the error message carefully. It usually tells you if the Project ID is wrong or if a permission is missing.</li>
    <li><b>Check Project ID:</b> Make sure your project ID in config.php (currently: <code>indicash-aff24</code>) matches exactly with the one inside <code>dmboos.json</code>.</li>
</ul>";

echo "</body></html>";