<?php

include('header.php');

if (in_array(16, $HiddenProducts)){



// Start the session for CSRF token protection

session_start();
if (isset($_GET['success']) && !empty($_SESSION['success_msg'])) {
    echo "<script>alert('".$_SESSION['success_msg']."');</script>";
    unset($_SESSION['success_msg']);
}


// Generate CSRF token if not set

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // Generate a random CSRF token

}



// Fetch current notice content from the database

$select = mysqli_query($con, "SELECT * FROM `content` ");

$row = mysqli_fetch_array($select);



// Handle form submission

// Handle form submission
if (isset($_POST['submit'])) {


    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {

        echo "<script>alert('Invalid CSRF token. Please refresh the page and try again.');</script>";

    } else {

        /* ================= PERMANENT MESSAGE ================= */
        if (isset($_POST['content'])) {

            $content = trim($_POST['content']);

            if (empty($content)) {
                echo "<script>alert('Content cannot be empty.');</script>";
            } 
            elseif (strlen($content) > 5000) {
                echo "<script>alert('Content is too long.');</script>";
            } 
            else {

                $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

                mysqli_query($con, "UPDATE content SET notice='$content' WHERE sn=1");
                $_SESSION['success_msg'] = 'Permanent message sent successfully';
echo "<script>window.location.href='notice.php?success=1';</script>";
exit;


                log_action('Permanent notice updated');

                echo "<script>window.location.href='notice.php';</script>";
            }
        }

        /* ================= MARQUEE MESSAGE ================= */
        if (isset($_POST['marquee'])) {

            $marquee = trim($_POST['marquee']);

            if (empty($marquee)) {
                echo "<script>alert('Marquee content cannot be empty.');</script>";
            } 
            else {

                $marquee = htmlspecialchars($marquee, ENT_QUOTES, 'UTF-8');

                $update = mysqli_query(
    $con, 
    "UPDATE content SET marquee='$marquee' WHERE sn=1"
);
$_SESSION['success_msg'] = 'Marquee message sent successfully';
echo "<script>window.location.href='notice.php?success=1';</script>";
exit;


if (!$update) {
    die('DB ERROR: ' . mysqli_error($con));
}


                log_action('Marquee message updated');

                echo "<script>window.location.href='notice.php';</script>";
            }
        }
    }
}



?>





<section class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-12">

                <h1 style="text-align: center;">Notice Board</h1>

            </div>

            <!--<div class="col-sm-6">-->

            <!--    <ol class="breadcrumb float-sm-right">-->

            <!--        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>-->

            <!--        <li class="breadcrumb-item active">Notice</li>-->

            <!--    </ol>-->

            <!--</div>-->

        </div>

    </div><!-- /.container-fluid -->

</section>



<!-- Main content -->

<section class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-md-1"></div>

            <div class="col-md-10">

                <!-- Form for updating notice -->

                <div class="card card-success">

                    <div class="card-header">

                        <h5>Permenent Message</h5>



                    </div>

                    <form method="POST">

                        <div class="card-body">

                            <div class="form-group">

                                <!-- CSRF Token input -->

                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">



                        

                                <textarea name="content" id="content" rows="5" class="form-control"><?php echo htmlspecialchars($row['notice'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                            </div>

                        </div>



                        <div class="card-footer">

                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>

                        </div>

                    </form>

                    <!-- /.card-body -->

                </div>

                <!-- /.card -->

            </div>

            <div class="col-md-1"></div>

        </div><!-- /.row -->

    </div><!-- /.container-fluid -->

</section>

<!-- /.content -->

<!-- Main content -->

<section class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-md-1"></div>

            <div class="col-md-10">

                <!-- Form for updating notice -->

                <div class="card card-success">

                    <div class="card-header">

                        <h5>Marquee Message</h5>

                    </div>

                    <form method="POST">

                        <div class="card-body">

                            <div class="form-group">

                                <!-- CSRF Token input -->

                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">



                        

                                <textarea name="marquee" id="content" rows="5" class="form-control"><?php echo htmlspecialchars($row['marquee'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                            </div>

                        </div>



                        <div class="card-footer">

                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>

                        </div>

                    </form>

                    <!-- /.card-body -->

                </div>

                <!-- /.card -->

            </div>

            <div class="col-md-1"></div>

        </div><!-- /.row -->

    </div><!-- /.container-fluid -->

</section>

<script src="https://cdn.ckeditor.com/4.25.0/standard/ckeditor.js"></script>

<script>

    CKEDITOR.replace('content');  // Initialize CKEditor for the content textarea

</script>



<?php 

}else{ 

echo "<script>

window.location.href = 'unauthorized.php';

</script>";

exit();

}

include('footer.php'); ?>