<?php include('header.php');
$market = $_REQUEST['market'];
?>
<style>
    .table td, .table th {
    white-space: nowrap;
}
@media (max-width:768px){

.hide-mobile{
display:none;
}

}
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Starline Games</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Starline Games</li>
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
                    <a href="#AddNewGame" data-toggle="modal" class="btn btn-primary">Add Game</a>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped table-sm">
                  <thead>
                  <tr>
                    <th class="hide-mobile">#</th>
                    <th>Game Name</th>
                    <th>Close Time</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php
                    $game = mysqli_query($con, "SELECT * FROM `starline_timings` where market='$market' ORDER BY sn ASC");
                    $i = 1;
                    while($row = mysqli_fetch_array($game)){
                  ?>
                    <tr>
                      <td class="hide-mobile"><?php echo $i; ?></td>
                      <td><?php echo $row['name']; ?></td>
                      <td>
                        <?php 
                          echo  date("h:i:sa", strtotime($row['close']));
                        ?>
                      </td>
                      
                      <td>
                        
                        <a href="starline-game-list.php?Delete=<?php echo $row['sn']; ?>&market=<?php echo $market; ?>" class="btn btn-sm btn-danger">Delete</a>
                      </td>
                      
                    </tr>

                 
                  <?php
                    $i++;
                    }

                    // Edit Game
                    if(isset($_GET['Delete'])){
                      $gameID = $_GET['Delete'];
                      
                      $updateGame = mysqli_query($con, "DELETE FROM `starline_timings` WHERE `sn`='$gameID'");
                      if($updateGame){
                          echo "<script>window.location.href='starline-game-list.php?market=".$market."';</script>";
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-primary">
            <div class="modal-header">
              <h4 class="modal-title">Add New Game</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="market" value="<?php echo $market; ?>" />
                    <div class="form-group">
                        <label>Game Name</label>
                        <input type="text" class="form-control" name="gameName" required />
                    </div>
                    <div class="form-group">
                        <label>Open Time</label>
                        <input type="time" class="form-control" name="closeTime" required />
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
  $closeTime = $_POST['closeTime'];
  $withdrawCloseTime24  = date("H:i", strtotime($closeTime));
  
  $CreateDate = date("Y-m-d H:i:s");
  
  $insert = mysqli_query($con, "INSERT INTO `starline_timings`( `name`, `market`, `open`, `close`, `auto`) 
                                                          VALUES ('$gameName','$market','','$closeTime','0')");
  
      
      if($insert){
          echo "<script>window.location.href ='starline-market-list.php?market=".$market."';</script>";
      }else{
          echo "<script>alert('Server Error. Please try again after some time!!');</script>";
      }
  
}

include('footer.php'); 
?>