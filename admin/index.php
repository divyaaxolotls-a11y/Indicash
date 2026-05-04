<?php
include('config.php');
session_start();
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['userID'])) {
    echo "<script>window.location= 'dashboard1.php';</script>";
}

if (isset($_POST['submit'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Sanitize email and password inputs to prevent XSS
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

    // Prepare SQL query to prevent SQL injection
    $stmt = $con->prepare("SELECT * FROM `admin` WHERE `email` = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->num_rows;

    if ($count > 0) {
        $log = $result->fetch_assoc();

        // Verify the password using password_verify
        if (password_verify($password, $log['password'])) {
            // Generate OTP
            // $otp = rand(1111,9999);
            $otp = 9999;

            // $_SESSION['otp'] = $otp;  // Store OTP in session temporarily
            // $_SESSION['otp_time'] = time(); // Store the time when OTP was generated (for expiration check)
            // $_SESSION['userID'] = $email;
            
            session_regenerate_id(true); // Secure session ID

$session_token = session_id(); // Or use bin2hex(random_bytes(32));

$_SESSION['otp'] = $otp;
$_SESSION['otp_time'] = time();
$_SESSION['userID'] = $email;
$_SESSION['session_token'] = $session_token;

// Invalidate all old sessions by updating token in DB
$updateStmt = $con->prepare("UPDATE `admin` SET `session_token` = ? WHERE `email` = ?");
$updateStmt->bind_param("ss", $session_token, $email);
$updateStmt->execute();
$updateStmt->close();


            // Send OTP to the registered mobile number
            //  $mobileNumber =9888195353; // Assuming 'mobile' field contains the user's phone number
            $mobileNumber =7219864404; // Assuming 'mobile' field contains the user's phone number

            $message = "Your OTP for login is: $otp";
            // wp_message($message, $mobileNumber);


            // Redirect to OTP verification page
            echo "<script>window.location='verify-otp.php';</script>";
        } else {
            echo "<script>alert('Invalid credentials.');</script>";
        }
    } else {
        echo "<script>alert('Invalid credentials.');</script>";
    }

    // Close the statement
    $stmt->close();
}

// OTP message function
function wp_message($message, $mobileNumber)
{
    $apiKey = '97f8952235574bc384a05d1ca9df248d';
    
    // URL encode the message to prevent invalid characters in the URL
    $encodedMessage = urlencode($message);

    // Prepare the URL with query parameters
    $url = "http://195.201.12.47/wapp/api/send?apikey=$apiKey&mobile=$mobileNumber&msg=$encodedMessage";

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
    }

    // Close cURL session
    curl_close($ch);

    // Print the response
    // echo "API Response: " . htmlspecialchars($response);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Login</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <style>
    body {
      background: linear-gradient(135deg, #667eea, #764ba2);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    .login-box {
      width: 100%;
      max-width: 400px;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
      text-align: center;
    }

    .login-box .logo {
      font-size: 32px;
      font-weight: bold;
      color: #333;
    }

    .login-box .input-group {
      margin-bottom: 15px;
    }

    .login-box input {
      height: 50px;
      font-size: 16px;
      border-radius: 8px;
    }

    .btn-primary {
      background: #667eea;
      border: none;
      border-radius: 8px;
      padding: 12px;
      font-size: 18px;
      font-weight: bold;
    }

    .btn-primary:hover {
      background: #5a67d8;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <div class="logo">
      <i class="fas fa-user-shield"></i> Admin Panel
    </div>
    <p class="text-muted mt-2">Sign in to continue</p>

    <!-- Login Form -->
    <form method="POST" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
        <input type="text" name="email" class="form-control" placeholder="Email" required />
      </div>

      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-lock"></i></span>
        <input type="password" name="password" class="form-control" placeholder="Password" required />
      </div>

      <button type="submit" name="submit" class="btn btn-primary w-100">Sign In</button>
    </form>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

