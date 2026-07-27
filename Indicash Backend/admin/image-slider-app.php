<?php 
include('header.php');
session_start();
if (in_array(9, $HiddenProducts)){

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<script>
    var csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>';
</script>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Image Slider</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Image Slider</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <a href="#AddNewGame" data-toggle="modal" class="btn btn-primary">Add Image</a>
                        </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Type</th>
                                    <th>Redirect</th>
                                    <th>Redirect to</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $game = mysqli_query($con, "SELECT * FROM `image_slider` ORDER BY sn ASC");
                                $i = 1;
                                while ($row = mysqli_fetch_array($game)) {
                                    $imageSrc = htmlspecialchars($row['image']); // Escape output
                                    $verifyStatus = $row['verify'] == "1" ? "Verified" : "Unverified";
                                    $redirect = htmlspecialchars($row['refer']); // Escape output
                                    $data = htmlspecialchars($row['data']); // Escape output
                                ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td> <a href="<?php echo $imageSrc; ?>"><img src="<?php echo $imageSrc; ?>" style="height:100px" /></a></td>
                                        <td><?php echo $verifyStatus; ?></td>
                                        <td><?php echo $redirect; ?></td>
                                        <td><?php echo $data; ?></td>
                                        <td>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['sn']; ?>, '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>');" class="btn btn-sm btn-danger">Delete</a>
                                        </td>
                                    </tr>
                                <?php
                                    $i++;
                                }
                                ?>
                            </tbody>
                        </table>
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

<!-- Add New Image Modal -->
<div class="modal fade" id="AddNewGame">
    <div class="modal-dialog">
        <div class="modal-content bg-primary">
            <div class="modal-header">
                <h4 class="modal-title">Add New Image</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />

                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Image</label>
                        <input type="file" class="form-control" name="fileToUpload" required />
                    </div>

                    <div class="form-group">
                        <label>Image Type</label>
                        <select id="verify" name="verify" class="form-control" style="width: 100%;">
                            <option value="" selected disabled>Select Type</option>
                            <option value="1">Verified users</option>
                            <option value="0">Unverified users</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Redirect Type</label>
                        <select id="redirect" name="redirect" class="form-control" onchange='redirect_sel(this.value)' style="width: 100%;">
                            <option value="" selected>No Redirect</option>
                            <option value="market">Market Redirect</option>
                            <option value="refer">Refer Redirect</option>
                            <option value="url">URL Redirect</option>
                        </select>
                    </div>

                    <script>
                        function redirect_sel(refer) {
                            if (refer == 'market') {
                                $('#market_block').show();
                                $('#url_block').hide();
                            } else if (refer == 'url') {
                                $('#market_block').hide();
                                $('#url_block').show();
                            } else {
                                $('#market_block').hide();
                                $('#url_block').hide();
                            }
                        }
                    </script>

                    <div class="col-md-12" id='market_block' style="display:none;">
                        <div class="form-group">
                            <label>Game Name</label>
                            <select id="game_id" name='game_id' class="form-control select2bs4" style="width: 100%;">
                                <option value="" selected disabled>Select Game</option>
                                <?php
                                $gameList = mysqli_query($con, "SELECT * FROM `gametime_new` WHERE `active`='1' ORDER BY sn DESC");
                                while ($row = mysqli_fetch_array($gameList)) {
                                    $market = htmlspecialchars($row['market']); // Escape output
                                ?>
                                    <option value="<?php echo $market; ?>"><?php echo $market; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id='url_block' style="display:none;">
                        <label>Enter URL</label>
                        <input type="text" class="form-control" name="url" />
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
                    <button type="submit" name="CreateNew" class="btn btn-outline-light">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

// Handle Image Deletion
if (isset($_GET['Delete'])) {
    // Validate CSRF token
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>
            alert('Invalid CSRF token. Please refresh the page and try again.');
            window.location.href = 'image-slider-app.php';
        </script>";
        exit;
    }

    // Sanitize the game ID
    $gameID = intval($_GET['Delete']);  // Ensure it's an integer

    // Perform the delete operation
    $deleteQuery = "DELETE FROM `image_slider` WHERE `sn` = $gameID";
    $updateGame = mysqli_query($con, $deleteQuery);

    $remark = 'Image Slider Deleted';
    log_action($remark);
    if ($updateGame) {
        echo "<script>
            alert('Record deleted successfully.');
            window.location.href = 'image-slider-app.php';
        </script>";
    } else {
        echo "<script>
            alert('Error deleting record.');
            window.location.href = 'image-slider-app.php';
        </script>";
    }
}

// Handle new image creation
if (isset($_POST['CreateNew'])) {
    if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>
            alert('Invalid CSRF token. Please refresh the page and try again.');
            window.location.href = 'image-slider-app.php';
        </script>";
        exit;
    }

    $target_dir = "../upload/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate image file type (JPEG, PNG, GIF only)
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        $uploadOk = 0;
    }

    // Validate image file size (max 5MB)
    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        echo "<script>alert('Sorry, your file is too large. Max size is 5MB.');</script>";
        $uploadOk = 0;
    }

    // Form data
    $verify = htmlspecialchars($_POST['verify']); // Escape output
    $redirect = htmlspecialchars($_POST['redirect']); // Escape output
    $data = '';

    if ($redirect == "market") {
        $data = htmlspecialchars($_POST['game_id']); // Escape output
    } else if ($redirect == "url") {
        $data = htmlspecialchars($_POST['url']); // Escape output
    }

    // If upload is OK, move the file and insert data
    if ($uploadOk == 1 && move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $insert = mysqli_query($con, "INSERT INTO `image_slider`( `image`, `verify`, `refer`, `data`) 
            VALUES ('$target_file', '$verify', '$redirect', '$data')");

    $remark = 'New Image Slider Added';
    log_action($remark);
    
        if ($insert) {
            echo "<script>window.location.href = 'image-slider-app.php';</script>";
        } else {
            echo "<script>alert('Server Error. Please try again after some time!!');</script>";
        }
    } else {
        echo "<script>alert('Error uploading image. Please try again.');</script>";
    }
}
 }else{ 

 echo "<script>
                        window.location.href = 'unauthorized.php';
                    </script>";
    exit();
}
include('footer.php');
?>
<script type="text/javascript">
    // Function to handle the delete action with CSRF token
    function confirmDelete(sn, csrfToken) {
        if (confirm("Are you sure you want to delete this record?")) {
            // Redirect with CSRF token and record ID as query parameters
            window.location.href = `image-slider-app.php?Delete=${sn}&csrf_token=${csrfToken}`;
        }
    }

    // Optional: You can wrap this in $(document).ready() if you use jQuery to ensure it's loaded after the DOM is ready
    $(document).ready(function() {
        // Your additional JavaScript if needed
    });
</script>
