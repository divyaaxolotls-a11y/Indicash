<?php include('header.php'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Front end management</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Front end management</li>
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
              <div class="card-header">
                <h3 class="card-title">
                  <p>Image in between every 5 results</p>
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
                    <th>URL</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php
                    $game = mysqli_query($con, "SELECT * FROM `image_slider_website` ORDER BY sn ASC");
                    $i = 1;
                    while($row = mysqli_fetch_array($game)){
                  ?>
                    <tr>
                      <td><?php echo $i; ?></td>
                      
                      <td> <a href="<?php echo $row['image']; ?>"><img src="<?php echo $row['image']; ?>" style="height:100px" /></a></td>
                  
                      <td><?php echo $row['url']; ?></td>
                      <td>
                          
                        <a href="image_slider.php?Delete=<?php echo $row['sn']; ?>" class="btn btn-sm btn-danger">Delete</a>
                      </td>
                      
                    </tr>

                  <!-- Edit Game -->
                  <?php
                    $i++;
                    }

                    // Edit Game
                    if(isset($_GET['Delete'])){
                      $gameID = $_GET['Delete'];
                      
                      $updateGame = mysqli_query($con, "DELETE FROM `image_slider_website` WHERE `sn`='$gameID'");
                      if($updateGame){
                          echo "<script>window.location.href='image_slider.php';</script>";
                      }
                      
                  }
                  
                  
                  ?>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            
            
            
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <p>Banner 1</p>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <img style="max-width: -webkit-fill-available;" src="<?php $get_banner_1 = mysqli_fetch_array(mysqli_query($con,"select * from settings where data_key='banner_1'")); echo $get_banner_1['data'] ?>"/>
               <form method="POST"  enctype="multipart/form-data" enctype="multipart/form-data">
                  <div class="modal-body">
                      <div class="form-group">
                          <label>Select Image</label>
                          <input type="file" class="form-control" name="fileToUpload"  />
                      </div>
 						<div class="form-group">
                          <label>Redirect URL</label>
                          <input type="text" class="form-control" value="<?php $get_banner_1_url = mysqli_fetch_array(mysqli_query($con,"select * from settings where data_key='banner_1_url'")); echo $get_banner_1_url['data'] ?>" name="banner_1_url" />
                      </div>
                    
                  </div>
                  <div class="modal-footer justify-content-between">
                      <button type="submit" name="CreateNew1" class="btn btn-primary">Save changes</button>
                  </div>
              </form>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            
            
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <p>Banner 2</p>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                  <img style="max-width: -webkit-fill-available;" src="<?php $get_banner_2 = mysqli_fetch_array(mysqli_query($con,"select * from settings where data_key='banner_2'")); echo $get_banner_2['data'] ?>"/>
            
               <form method="POST"  enctype="multipart/form-data" enctype="multipart/form-data">
                  <div class="modal-body">
                      <div class="form-group">
                          <label>Select Image</label>
                          <input type="file" class="form-control" name="fileToUpload"  />
                      </div>
                      <div class="form-group">
                          <label>Redirect URL</label>
                          <input type="text" class="form-control" value="<?php $get_banner_2_url = mysqli_fetch_array(mysqli_query($con,"select * from settings where data_key='banner_2_url'")); echo $get_banner_2_url['data'] ?>" name="banner_2_url" />
                      </div>
                    
                  </div>
                  <div class="modal-footer justify-content-between">
                      <button type="submit" name="CreateNew2" class="btn btn-primary">Save changes</button>
                  </div>
              </form>
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

<!-- Add New Game -->
<div class="modal fade" id="AddNewGame">
    <div class="modal-dialog">
        <div class="modal-content bg-primary">
            <div class="modal-header">
              <h4 class="modal-title">Add New Image</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST"  enctype="multipart/form-data" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Image</label>
                        <input type="file" class="form-control" name="fileToUpload"  />
                    </div>
                  <div class="form-group">
                          <label>Redirect URL</label>
                          <input type="text" class="form-control" name="banner_url" />
                      </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" name="CreateNew" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
          <!-- /.modal-content -->
    </div>
        <!-- /.modal-dialog -->
</div>

<?php 

if(isset($_POST['CreateNew'])){
   
    $target_dir = "upload/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  $banner_url = $_POST['banner_url'];
    
    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);

  $insert = mysqli_query($con, "INSERT INTO `image_slider_website`( `image`,`url`) 
                                                          VALUES ('$target_file','$banner_url')");
      if($insert){
          echo "<script>window.location.href = 'image_slider.php';</script>";
      }else{
          echo "<script>alert('Server Error. Please try again after some time!!');</script>";
      }
  
}

if(isset($_POST['CreateNew1'])){
   
    $target_dir = "upload/";
  
  if(strlen($_FILES["fileToUpload"]["name"]) > 0){
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  
    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);

  $insert = mysqli_query($con, "update settings set data='$target_file' where data_key='banner_1'");
  }
  
  $banner_1_url = $_POST['banner_1_url'];
  $insert = mysqli_query($con, "update settings set data='$banner_1_url' where data_key='banner_1_url'");
  
  echo "update settings set data='$target_file' where data_key='banner_1'";
      if($insert){
          echo "<script>window.location.href = 'image_slider.php';</script>";
      }else{
          echo "<script>alert('Server Error. Please try again after some time!!');</script>";
      }
  
}

if(isset($_POST['CreateNew2'])){
   
    $target_dir = "upload/";
  if(strlen($_FILES["fileToUpload"]["name"]) > 0){
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  
    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);

  $insert = mysqli_query($con, "update settings set data='$target_file' where data_key='banner_2'");
  }
  
  $banner_2_url = $_POST['banner_2_url'];
  $insert = mysqli_query($con, "update settings set data='$banner_2_url' where data_key='banner_2_url'");
      if($insert){
          echo "<script>window.location.href = 'image_slider.php';</script>";
      }else{
          echo "<script>alert('Server Error. Please try again after some time!!');</script>";
      }
  
}
include('footer.php'); 
?>