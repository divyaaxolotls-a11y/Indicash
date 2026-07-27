<?php
session_start(); // Start the session at the top
include('header.php');

if (in_array(20, $HiddenProducts)){

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

if (isset($_POST['submit'])) {

    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Invalid CSRF token. Please refresh the page and try again.');</script>";
    } else {
        // Sanitize and validate inputs
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $message = trim($_POST['message']);
        $rating = trim($_POST['rating']);

        // Validate fields
        if (empty($name) || empty($email) || empty($message) || empty($rating)) {
            echo "<script>alert('All fields are required.');</script>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Invalid email format.');</script>";
        } elseif ($rating < 1 || $rating > 5) {
            echo "<script>alert('Rating must be between 1 and 5.');</script>";
        } else {
            // Use htmlspecialchars to prevent XSS attacks
            $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
            $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

            // Sanitize and prepare data for insertion into the database
            $name = mysqli_real_escape_string($con, $name);
            $email = mysqli_real_escape_string($con, $email);
            $message = mysqli_real_escape_string($con, $message);
            $rating = mysqli_real_escape_string($con, $rating);

            // Prepare the SQL query
            $sql = "INSERT INTO reviews_app (name, email, message, rating_star) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $sql);
            
               $remark = 'New review is added';
                log_action($remark); 

            if ($stmt) {
                // Bind parameters to the query
                mysqli_stmt_bind_param($stmt, 'sssi', $name, $email, $message, $rating);

                // Execute the query
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success'] = "Details submitted successfully";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $error = "Error: " . mysqli_error($con);
                }

                // Close the statement
                mysqli_stmt_close($stmt);
            } else {
                $error = "Error preparing statement: " . mysqli_error($con);
            }
        }
    }
}
?>


<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Add Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Submit Details</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-danger">
                        Submit Details
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php 
                        if (isset($_SESSION['success'])) { ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php } ?>

                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                    <!-- Name Field -->
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name" placeholder="Enter Name" required/>
                                    </div>

                                    <!-- Email Field -->
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="email" placeholder="Enter Email" required/>
                                    </div>

                                    <!-- Message Field -->
                                    <div class="form-group">
                                        <label>Message</label>
                                        <textarea name="message" rows="5" class="form-control" placeholder="Enter Message" required></textarea>
                                    </div>

                                    <!-- Rating Field -->
                                    <div class="form-group">
                                        <label>Rating</label>
                                        <input type="number" class="form-control" name="rating" placeholder="Enter Rating (1-5)" min="1" max="5" required/>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group">
                                        <button type="submit" name="submit" class="btn btn-primary btn-block">Submit</button>
                                    </div>
                                </form>
                            </div>
                            <!-- /.col -->
                            <div class="col-md-3"></div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->

<?php 
}else{ 
echo "<script>
window.location.href = 'unauthorized.php';
</script>";
exit();
}
include('footer.php'); ?>

<script>
    $(function () {
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });
    });
</script>
