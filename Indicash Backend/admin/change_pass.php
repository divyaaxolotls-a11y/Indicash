<?php 
include('header.php'); 
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

if (isset($_GET['userID'])) {
    $mobile = $_GET['userID'];
} else {
    echo "User not found.";
    exit;
}

if (isset($_POST['change_password'])) {
    
   if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_POST['csrf_token']) {
    // Alert the user and redirect back to transaction.php
    echo "<script>
        alert('Invalid CSRF token. Please refresh the page and try again.');
    </script>";
    exit; // Stop further execution
}
 
    
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        $hashed_password = md5($new_password);

        $query = "UPDATE users SET password = ? WHERE mobile = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ss", $hashed_password, $mobile);

        if ($stmt->execute()) {
            echo "<script>alert('Password changed successfully'); window.location.href='users_old.php';</script>";
        } else {
            echo "<script>alert('Error changing password. Please try again.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Passwords do not match. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <!-- Add Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            /*padding-top: 50px;*/
        }
        .container {
            max-width: 600px;
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container">
    
    <form action="" method="POST">
    <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

       <div class="form-group">
          <label for="new_password">New Password:</label>
          <input type="password" class="form-control" name="new_password" id="new_password" minlength="4" maxlength="4" pattern="\d{4}" required>
          <small id="new_password_help" class="form-text text-muted">Password must be exactly 4 digits.</small>
       </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" class="form-control" name="confirm_password" id="confirm_password" minlength="" maxlength="4" pattern="\d{4}" required>
        <small id="confirm_password_help" class="form-text text-muted">Password must be exactly 4 digits.</small>
     </div>

      <button type="submit" class="btn btn-primary" name="change_password">Change Password</button>
    </form>
</div>

<!-- Add Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php include('footer.php'); ?>
