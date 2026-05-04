<?php 
include('header.php'); 
if (in_array(14, $HiddenProducts)){

if (isset($_POST['ChangePassword'])) {
    $oldPass = isset($_POST['Oldpassword']) ? $_POST['Oldpassword'] : '';
    $newPass = isset($_POST['Newpassword']) ? $_POST['Newpassword'] : '';
    $confirmPass = isset($_POST['Confirmpassword']) ? $_POST['Confirmpassword'] : '';

    // Server-side password validation (strong password policy)
    $passwordError = "";
    // $strongPasswordRegex = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";  // Password must include at least one letter, one number, and one special character.

    // // Check if password is strong
    // if (!preg_match($strongPasswordRegex, $newPass)) {
    //     $passwordError = "Password must be at least 8 characters long and include one letter, one number, and one special character.";
    // }

    // Check if new password matches confirm password
    if ($newPass !== $confirmPass) {
        $passwordError = "Your password and confirm password do not match. Please try again.";
    }

    // Check if all fields are filled
    if (empty($oldPass) || empty($newPass) || empty($confirmPass)) {
        $passwordError = "All fields are required.";
    }

    if ($passwordError === "") {
        // Sanitize input for security (escape output using htmlspecialchars)
        $oldPass = mysqli_real_escape_string($con, htmlspecialchars(trim($oldPass)));
        $newPass = mysqli_real_escape_string($con, htmlspecialchars(trim($newPass)));
        $confirmPass = mysqli_real_escape_string($con, htmlspecialchars(trim($confirmPass)));

        // CSRF Protection: Validate CSRF token
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo "<script>alert('Invalid CSRF token.');</script>";
            exit;
        }

        // Get the current password from the database to verify the old password
        $result = mysqli_query($con, "SELECT `password` FROM `admin` WHERE `email` = 'admin@gmail.com'");  // Replace with actual session or admin email
        $row = mysqli_fetch_assoc($result);

        // Verify the old password
        if (password_verify($oldPass, $row['password'])) {
            // Hash the new password before saving it
            $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);

            // Update the password in the database
            $update = mysqli_query($con, "UPDATE `admin` SET `password`='$hashedPassword' WHERE `email` = 'contact@info.department@dmboss.com'");  // Replace with actual session or admin email

            $remark = 'Admin Password Changed';
            log_action($remark);  // Call the function to log the action
            if ($update) {
                echo "<script>alert('Your password has been successfully changed!');</script>";
                  // Destroy the session after password change
                session_unset(); // Remove all session variables
                session_destroy(); // Destroy the session
                echo "<script>window.location.href= 'change-password.php';</script>";
            } else {
                echo "<script>alert('Server error. Please try again later.');</script>";
            }
        } else {
            echo "<script>alert('The old password is incorrect. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('$passwordError');</script>";
    }
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // Generate a random CSRF token
}
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Change Password</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Change Password</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3"></div>
      <div class="col-md-6">
        <!-- Form Element sizes -->
        <div class="card card-success">
          <div class="card-header">
            <h3 class="card-title">Change Password</h3>
          </div>
          <form method="post">
            <div class="row p-b-30">
              <div class="col-12">
                <!-- Old Password Input -->
                <div class="input-group mb-3">
                  <input type="password" name="Oldpassword" class="form-control form-control-lg" placeholder="Old Password" aria-label="Password" required>
                </div>

                <!-- New Password Input -->
                <div class="input-group mb-3">
                  <input type="password" name="Newpassword" class="form-control form-control-lg" placeholder="New Password" aria-label="Password" required minlength="8">
                </div>
                <p class="text-danger">Password must be at least 8 characters long and include one letter, one number, and one special character.</p>

                <!-- Confirm Password Input -->
                <div class="input-group mb-3">
                  <input type="password" name="Confirmpassword" class="form-control form-control-lg" placeholder="Confirm Password" aria-label="Password" required minlength="8">
                </div>
              </div>
            </div>

            <!-- CSRF Token Hidden Field -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <div class="p-t-20">
                    <button class="btn btn-success float-right" name="ChangePassword" type="submit">Submit</button>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="col-md-3"></div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<?php 
}else{
echo "<script>
window.location.href = 'unauthorized.php';
</script>";
}
exit();
include('footer.php'); ?>

