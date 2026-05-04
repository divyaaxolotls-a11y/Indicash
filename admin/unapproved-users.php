<?php include('header.php'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Un-approved Users</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Un-approved Users</li>
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
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>User Name</th>
                      <th>User Mobile</th>
                      <th>Points</th>
                      <th>Registration Date</th>
                      <th>Betting</th>
                      <th>Point Transfer</th>
                      <th>Active</th>
                      <th>View</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    $user = mysqli_query($con, "SELECT * FROM `users` WHERE `verify`='0' ORDER BY sn DESC");
                    $i = 1;
                    while($row = mysqli_fetch_array($user)){
                  ?>
                    <tr>
                      <td><?php echo $i; ?></td>
                      <td><?php echo $row['name']; ?></td>
                      <td>
                        <?php echo $row['mobile']; ?><br>
                        <a href="https://api.whatsapp.com/send?phone=91<?php echo $row['mobile']; ?>" target="_blank"><i class="fab fa-whatsapp" style="color:green;font-size:20px;"></i></a>
                        &nbsp;&nbsp;
                        <a href="tel:+91 <?php echo $row['mobile']; ?>" target="_blank"><i class="fas fa-phone-alt" style="font-size:20px;"></i></a>
                      </td>
                      <td><?php echo $row['wallet']; ?></td>
                      <td><?php echo date('d-m-Y',$row['created_at']); ?></td>
                      <td>
                        <?php
                          if($row['verify'] == 0){
                        ?>
                          <a href="unapproved-users.php?BettingActive=<?php echo $row['sn']; ?>" class="btn btn-sm btn-danger">No</a>
                        <?php
                          }else{
                        ?>
                          <a href="unapproved-users.php?BettingDeactive=<?php echo $row['sn']; ?>" class="btn btn-sm btn-success">Yes</a>
                        <?php
                          }
                        ?>
                      </td>
                      <td>
                        <?php
                          if($row['transfer_points_status'] == 1){
                        ?>
                          <a href="unapproved-users.php?TransferDeactive=<?php echo $row['sn']; ?>" class="btn btn-sm btn-success">Yes</a>
                        <?php
                          }else{
                        ?>
                          <a href="unapproved-users.php?TransferActive=<?php echo $row['sn']; ?>" class="btn btn-sm btn-danger">No</a>
                        <?php
                          }
                        ?>
                      </td>
                      <td>
                        <?php
                          if($row['active'] == 0){
                        ?>
                            <a href="unapproved-users.php?UserActive=<?php echo $row['sn']; ?>" class="btn btn-sm btn-danger">No</a>
                        <?php
                          }else{
                        ?>
                            <a href="unapproved-users.php?UserDeactive=<?php echo $row['sn']; ?>" class="btn btn-sm btn-success">Yes</a>
                        <?php
                          }
                        ?>
                      </td>
                      <td>
                        <a href="user-profile.php?userID=<?php echo $row['mobile']; ?>"><i class="fas fa-eye" style="font-size:25px;"></i></a>
                      </td>
                    </tr>
                  <?php
                    $i++;  
                    }

                    // Active Transfer Status
                    if(isset($_GET['TransferActive'])){
                        $id = $_GET['TransferActive'];
                        $registrationDate = date("Y-m-d H:i:s");
                        
                        $updateUser = mysqli_query($con, "UPDATE `users` SET `transfer_points_status`='1' WHERE `sn`='$id'");
                        if($updateUser){
                            echo "<script>window.location.href='unapproved-users.php';</script>";
                        }
                        
                    }
                    
                    // Deactive Transfer Status
                    if(isset($_GET['TransferDeactive'])){
                        $id = $_GET['TransferDeactive'];
                        $registrationDate = date("Y-m-d H:i:s");
                        
                        $updateUser = mysqli_query($con, "UPDATE `users` SET `transfer_points_status`='0' WHERE `sn`='$id'");
                        if($updateUser){
                            echo "<script>window.location.href='unapproved-users.php';</script>";
                        }
                        
                    }
                    
                    // Active Betting Status
                    if(isset($_GET['BettingActive'])){
                        $id = $_GET['BettingActive'];
                        $registrationDate = date("Y-m-d H:i:s");
                        
                        $updateUser = mysqli_query($con, "UPDATE `users` SET `verify`='1' WHERE `sn`='$id' AND `verify`='0'");
                        if($updateUser){
                            echo "<script>window.location.href='unapproved-users.php';</script>";
                        }
                        
                    }
                    
                    // Deactive Betting Status
                    if(isset($_GET['BettingDeactive'])){
                        $id = $_GET['BettingDeactive'];
                        $registrationDate = date("Y-m-d H:i:s");
                        
                        $updateUser = mysqli_query($con, "UPDATE `users` SET `verify`='0' WHERE `sn`='$id' AND `verify`='1'");
                        if($updateUser){
                            echo "<script>window.location.href='unapproved-users.php';</script>";
                        }
                        
                    }
                    
                    
                    // Active User Status
                    if(isset($_GET['UserActive'])){
                        $id = $_GET['UserActive'];
                        $registrationDate = date("Y-m-d H:i:s");
                        
                        $updateUser = mysqli_query($con, "UPDATE `users` SET `active`='1' WHERE `user_id`='$id' AND `active`='0'");
                        if($updateUser){
                            echo "<script>window.location.href='unapproved-users.php';</script>";
                        }
                        
                    }
                    
                    // Deactive User Status
                    if(isset($_GET['UserDeactive'])){
                        $id = $_GET['UserDeactive'];
                        $registrationDate = date("Y-m-d H:i:s");
                        
                        $updateUser = mysqli_query($con, "UPDATE `users` SET `active`='0' WHERE `user_id`='$id' AND `active`='1'");
                        if($updateUser){
                            echo "<script>window.location.href='unapproved-users.php';</script>";
                        }
                        
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