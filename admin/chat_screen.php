<?php include('header.php'); 

$user = $_REQUEST['user'];

if(isset($_REQUEST['msg'])){
  $msg = $_REQUEST['msg'];
  
	mysqli_query($con,"INSERT INTO `admin_chats`(`user`, `message`, `seen`, `msg_to`, `created_at`) VALUES ('admin','$msg','0','$user','$stamp')");
  header("location:chat_screen.php?user=$user");
}

?>

<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Chats</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Chats</li>
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
                <h3 class="card-title" style="width: 100%;">
                    
                  
              <form method="post">
               <div class="form-group">
                    <label>Send Message</label>
                    <textarea type="number" name="msg"  class="form-control" required ></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>

              </form>
                  
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Sender</th>
                      <th>Message</th>
                      <th>Send time</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    $user = mysqli_query($con, "SELECT * FROM `admin_chats` where user='$user' OR msg_to='$user' ORDER BY sn desc");
                    $i = 1;
                    while($row = mysqli_fetch_array($user)){
                      
                  ?>
                    <tr>
                      <td><?php echo $i; ?></td>
                      <td><?php if($row['user']=="admin"){ echo 'Admin'; } else { echo 'User'; } ?></td>
                      <td><?php echo $row['message']; ?></td>
                    
                      <td><?php echo date('h:i A d/m/Y',$row['created_at']); ?></td>
                      
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

<?php include('footer.php'); ?>