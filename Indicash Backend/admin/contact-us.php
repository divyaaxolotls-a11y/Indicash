<?php include('header.php'); 
if (in_array(14, $HiddenProducts)){
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Contact Us</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Contact Details</li>
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
                        <h3 class="card-title">Contact Details</h3>
                    </div>
                    <?php
                    $select  = mysqli_query($con, "SELECT * FROM `settings` WHERE data_key='whatsapp'");
                    $row = mysqli_fetch_array($select);
                    $count = mysqli_num_rows($select);
                    ?>
                    <form method="POST">
                        <div class="card-body">
                            <div class="form-group">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">WhatsApp Number</label>
                                    <input type="number" min="0" value="<?php echo htmlspecialchars($row['data']); ?>" name="whatsapp" maxlength="10" class="form-control" placeholder="Enter WhatsApp Number" required />
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" name="AddValues" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
                
                <?php
                if (isset($_POST['AddValues'])) {
                    // CSRF token validation
                    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                        echo "<script>alert('Invalid CSRF token. Please refresh the page and try again.');</script>";
                    } else {
                        // Validate WhatsApp input
                        $whatsapp = isset($_POST['whatsapp']) ? htmlspecialchars(trim($_POST['whatsapp'])) : '';

                        // Check if the input is numeric and has exactly 10 digits
                        if (!is_numeric($whatsapp) || strlen($whatsapp) != 10) {
                            echo "<script>alert('Please enter a valid 10-digit WhatsApp number.');</script>";
                        } else {
                            // Proceed to update the database if the input is valid
                            $update = mysqli_query($con, "UPDATE `settings` SET `data`='$whatsapp' WHERE `data_key`='whatsapp'");
                            
                            $remark = 'Contact Us setting Updated';
                            log_action($remark);  // Call the function to log the action
                
                            if ($update) {
                                echo "<script>window.location.href= 'contact-us.php';</script>";
                            } else {
                                echo "<script>alert('Error updating the contact details.');</script>";
                            }
                        }
                    }
                }
                ?>

            </div>
            <div class="col-md-3"></div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?php }else{ 
echo "<script>
window.location.href = 'unauthorized.php';
</script>";
exit();
}
include('footer.php'); ?>
