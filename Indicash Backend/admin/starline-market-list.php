<?php include('header.php'); ?>
<style>
    .table td, .table th {
    white-space: nowrap;
}
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Starline Markets</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Starline Markets</li>
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
                    <a href="#AddNewGame" data-toggle="modal" class="btn btn-primary">Add Market</a>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <div class="table-responsive">
                 <table id="example1" class="table table-bordered table-striped table-sm">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>Game Name</th>
                    <th>Timings</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Rate</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php
                    $game = mysqli_query($con, "SELECT * FROM `starline_markets` ORDER BY name ASC");
                    $i = 1;
                    while($row = mysqli_fetch_array($game)){
                  ?>
                    <tr>
                      <td><?php echo $i; ?></td>
                      <td><?php echo $row['name']; ?></td>
                      
                      <td> <a href="starline-game-list.php?market=<?php echo $row['name']; ?>" class="btn btn-sm btn-success">Timings</a></td>
                      
                      <td class="text-center">
                          
                       
                        
                        <?php
                          if($row['active'] == 0){
                        ?>
                          <a href="starline-market-list.php?Active=<?php echo $row['sn']; ?>" class="btn btn-sm btn-danger">No</a>
                        <?php
                          }else{
                        ?>
                          <a href="starline-market-list.php?Deactive=<?php echo $row['sn']; ?>" class="btn btn-sm btn-success">Yes</a>
                        <?php
                          }
                        ?>
                        
                      </td>
                      <td>
                          
                        <a href="starline-market-list.php?Delete=<?php echo $row['sn']; ?>" class="btn btn-sm btn-danger">Delete</a>
                      </td>
                        <td>
                            <a href="set_rate.php?market=<?php echo urlencode($row['name']); ?>" class="btn btn-danger btn-sm">Rates</a>                        </td>
                    </tr>

                  <!-- Edit Game -->
                  <?php
                    $i++;
                    }

                    // Edit Game
                    if(isset($_GET['Delete'])){
                      $gameID = $_GET['Delete'];
                      
                      $updateGame = mysqli_query($con, "DELETE FROM `starline_markets` WHERE `sn`='$gameID'");
                      if($updateGame){
                          echo "<script>window.location.href='starline-market-list.php';</script>";
                      }
                      
                  }
                  
                  // Active User Status
                  if(isset($_GET['Active'])){
                      $id = $_GET['Active'];
                      
                      $updateUser = mysqli_query($con, "UPDATE `starline_markets` SET `active`='1' WHERE `sn`='$id'");
                      if($updateUser){
                          echo "<script>window.location.href='starline-market-list.php';</script>";
                      }
                      
                  }
                  
                  // Deactive User Status
                  if(isset($_GET['Deactive'])){
                      $id = $_GET['Deactive'];
                      
                      $updateUser = mysqli_query($con, "UPDATE `starline_markets` SET `active`='0' WHERE `sn`='$id'");
                      if($updateUser){
                          echo "<script>window.location.href='starline-market-list.php';</script>";
                      }
                      
                  }
                  
                  ?>
                  </tbody>
                </table>
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

<!-- Add New Game -->
<div class="modal fade" id="AddNewGame">
    <div class="modal-dialog">
        <div class="modal-content bg-primary">
            <div class="modal-header">
              <h4 class="modal-title">Add New Market</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Game Name</label>
                        <input type="text" class="form-control" name="gameName" required />
                    </div>
                    
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
                    <button type="submit" name="CreateNew" class="btn btn-outline-light">Save changes</button>
                </div>
            </form>
        </div>
          <!-- /.modal-content -->
    </div>
        <!-- /.modal-dialog -->
</div>

<?php 

if(isset($_POST['CreateNew'])){
  $gameName = $_POST['gameName'];
  
  $insert = mysqli_query($con, "INSERT INTO `starline_markets`( `image`, `name`, `days`, `active`) 
                                                          VALUES ('','$gameName','','1')");
      if($insert){
          echo "<script>window.location.href = 'starline-game-list.php';</script>";
      }else{
          echo "<script>alert('Server Error. Please try again after some time!!');</script>";
      }
  
}

include('footer.php'); 
?>