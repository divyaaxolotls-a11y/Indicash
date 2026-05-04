<?php 
include('header.php');
if (in_array(14, $HiddenProducts)){

// Fetch current UPI ID and QR image from the database
$qr_data_query = mysqli_query($con, "SELECT upi_id, qr_image FROM qr_code LIMIT 1");
$qr_data = mysqli_fetch_array($qr_data_query);

$current_upi_id = $qr_data['upi_id'];
$current_qr_image = $qr_data['qr_image'];

// CSRF token handling
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate CSRF token if not set
}

if(isset($_POST['submit'])){
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Invalid CSRF token. Please refresh the page and try again.');</script>";
    } else {   
        // Sanitize and validate UPI ID
        $upi_id = isset($_POST['upi_id']) ? htmlspecialchars(trim($_POST['upi_id'])) : '';

        // Validate UPI ID (add any further validation for the format of UPI ID)
        if (empty($upi_id)) {
            echo "<script>alert('UPI ID is required.');</script>";
        } else {
            // File upload handling
            $target_dir = "../upload/";
            $uploadOk = 1;
            $target_file = $current_qr_image;

            if (isset($_FILES["fileToUpload"]) && strlen($_FILES["fileToUpload"]["name"]) > 0) {
                $file_name = basename($_FILES["fileToUpload"]["name"]);
                $target_file = $target_dir . $file_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                // Check if image file is a valid image
                $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                if ($check === false) {
                    echo "<script>alert('File is not an image.');</script>";
                    $uploadOk = 0;
                }

                // Validate file type
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($imageFileType, $allowed_types)) {
                    echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
                    $uploadOk = 0;
                }

                // Validate file size (max size: 2MB)
                if ($_FILES["fileToUpload"]["size"] > 2000000) {
                    echo "<script>alert('Sorry, your file is too large. Maximum allowed size is 2MB.');</script>";
                    $uploadOk = 0;
                }

                // Check if $uploadOk is set to 0 by an error
                if ($uploadOk === 0) {
                    echo "<script>alert('Sorry, your file was not uploaded.');</script>";
                } else {
                    // Attempt to upload the file
                    if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                        echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
                        $uploadOk = 0;
                    }
                }
            }

            if ($uploadOk) {
                // Update QR code and UPI ID in the database
                $update = mysqli_query($con, "UPDATE `qr_code` SET `upi_id`='$upi_id', `qr_image`='$target_file'");
                
                $remark = 'QR CODE setting Updated';
                log_action($remark);  // Call the function to log the action
                if ($update) {
                    echo "<script>
                        alert('Records updated successfully.');
                        window.location.href = 'qr_code.php';
                    </script>";
                } else {
                    echo "<script>alert('Error updating the records.');</script>";
                }
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
                <h1>SET QR CODE</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">SET QR CODE</li>
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
                        SET QR CODE
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    
                                    <div class="form-group">
                                        <label>UPI ID</label>
                                        <input type="text" class="form-control" name="upi_id" value="<?php echo htmlspecialchars($current_upi_id); ?>" placeholder="Enter UPI ID" required />
                                    </div>

                                    <div class="form-group">
                                        <label>Select Image</label>
                                        <input type="file" class="form-control" name="fileToUpload" />
                                    </div>

                                    <!-- Show current QR Code image -->
                                    <?php if($current_qr_image) { ?>
                                        <div class="form-group">
                                            <label>Current QR Code</label>
                                            <div>
                                                <a href="<?php echo htmlspecialchars($current_qr_image); ?>" target="_blank">
                                                    <img src="<?php echo htmlspecialchars($current_qr_image); ?>" alt="QR Code" style="max-width: 500px; max-height: 200px;" />
                                                </a>
                                            </div>
                                        </div>
                                    <?php } ?>

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
