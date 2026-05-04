// <?php
// include('config.php');
// session_start();
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// // Generate CSRF token if not already set
// if (empty($_SESSION['csrf_token'])) {
//     $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// }

// if (isset($_SESSION['userID'])) {
//     echo "<script>window.location= 'dashboard.php';</script>";
// }

// if (isset($_POST['submit'])) {
//     // Validate CSRF token
//     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//         die('Invalid CSRF token.');
//     }

//     $email = $_POST['email'];
//     $password = $_POST['password'];

//     // Sanitize email and password inputs to prevent XSS
//     $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
//     $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

//     // Handle "Remember Me" checkbox
//     if (isset($_POST['remamberMe'])) {
//         // Set cookies for email and password securely
//         setcookie('email', $email, time() + (86400 * 30), "/", "", isset($_SERVER['HTTPS']), true);
//         setcookie('password', $password, time() + (86400 * 30), "/", "", isset($_SERVER['HTTPS']), true);
//     }

//     // Prepare SQL query to prevent SQL injection
//     $stmt = $con->prepare("SELECT * FROM `admin` WHERE `email` = ?");
//     $stmt->bind_param("s", $email);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $count = $result->num_rows;

//     if ($count > 0) {
//         $log = $result->fetch_assoc();

//         // Verify the password using password_verify
//         if (password_verify($password, $log['password'])) {
//             session_regenerate_id(true); // Prevent session fixation
//             $_SESSION['userID'] = $log['email']; // Store user email in session
            
//             ////////////////////////////
//             $remark = 'Admin Login with email id '. $_POST['email'] .'and password '. $_POST['password'];
//             log_action($remark);  // Call the function to log the action

//             //////////////////////////////
            
//             echo "<script>window.location='dashboard.php';</script>";
//         } else {
//                         $remark = 'Invalid credentials with email id '. $_POST['email'] .'and password '. $_POST['password'];
//             log_action($remark);  // Call the function to log the action
//             echo "<script>alert('Invalid credentials.');</script>";
//         }
//     } else {
//         echo "<script>alert('Invalid credentials.');</script>";
//     }

//     // Close the statement
//     $stmt->close();
// }
// ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Matka | Log In</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="#" class="h1"><b>Admin</b>Panel</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Sign in to start your session</p>

      <!-- Login Form -->
      <form method="POST" autocomplete="off">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <!-- Email input -->
        <div class="input-group mb-3">
          <input type="text" name="email" class="form-control" value="<?php if (isset($_COOKIE["email"])) { echo htmlspecialchars($_COOKIE["email"]); } ?>" placeholder="Userid Or Email" required />
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>

        <!-- Password input -->
        <div class="input-group mb-3">
          <input type="password" name="password" value="<?php if (isset($_COOKIE["password"])) { echo htmlspecialchars($_COOKIE["password"]); } ?>" class="form-control" placeholder="Password" required />
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="row">
          <div class="col-12">
            <div class="icheck-primary">
              <input type="checkbox" id="remember" value="1" name="remamberMe">
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" name="submit" class="btn btn-primary btn-block">Sign In</button>
      </form>

    </div>
  </div>
</div>

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>
