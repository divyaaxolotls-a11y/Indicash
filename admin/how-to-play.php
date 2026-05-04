<?php include('header.php');
if (in_array(14, $HiddenProducts)){
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>How To Play</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">How To Play</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">
                <!-- Form Element sizes -->
                <?php
                $select = mysqli_query($con, "SELECT * FROM `content` ");
                $row = mysqli_fetch_array($select);
                ?>
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">How To Play</h3>
                    </div>
                    <form method="POST">
                        <div class="card-body">
                            <div class="form-group">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">         
                                <div class="form-group">
                                    <label>How To Play Content</label>
                                    <textarea name="content" id="content" rows="5" class="form-control"><?php echo htmlspecialchars($row['howtoplay']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                <?php
                if (isset($_POST['submit'])) {
                    // CSRF token validation
                    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                        echo "<script>alert('Invalid CSRF token. Please refresh the page and try again.');</script>";
                    } else {
                        // Validate content input
                        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

                        // Check if the content is not empty
                        if (empty($content)) {
                            echo "<script>alert('Please enter the content.');</script>";
                        } else {
                            // Escape content to prevent XSS
                            $escaped_content = htmlspecialchars($content);

                            // Update content in the database
                            $update = mysqli_query($con, "UPDATE `content` SET `howtoplay`='$escaped_content'");
                            
                            $remark = 'How to play setting Updated';
                            log_action($remark);  // Call the function to log the action
                            
                            if ($update) {
                                echo "<script>window.location.href='how-to-play.php';</script>";
                            } else {
                                echo "<script>alert('Error updating content. Please try again.');</script>";
                            }
                        }
                    }
                }
                ?>

            </div>
            <div class="col-md-1"></div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script src="https://cdn.ckeditor.com/4.25.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('content');
</script>

<?php }else{ 
echo "<script>
window.location.href = 'unauthorized.php';
</script>";
exit();
}
include('footer.php'); ?>
