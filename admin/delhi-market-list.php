<?php include('header.php'); 


    $week[] = "SUNDAY";
    $week[] = "MONDAY";
    $week[] = "TUESDAY";
    $week[] = "WEDNESDAY";
    $week[] = "THURSDAY";
    $week[] = "FRIDAY";
    $week[] = "SATURDAY";



if (isset($_REQUEST['submit']))
{
    extract($_REQUEST);
    
    $market = filter_var($market, FILTER_SANITIZE_STRING);
    
    
    if(mysqli_num_rows(mysqli_query($con,"select sn from gametime_delhi where market='$market'")) == 0){
        
        if($type == "Yes"){
            for($i = 0; $i < count($week);$i++){
                if($_REQUEST[$week[$i].'timetype'] == "close"){
                    $data[] = $week[$i]."(CLOSED)";
                } else if($_REQUEST[$week[$i].'open'] != $open || $_REQUEST[$week[$i].'close'] != $close){
                    
                        if($_REQUEST[$week[$i].'open'] != "" && $_REQUEST[$week[$i].'close'] != ""){
                        $data[] = $week[$i]."(".$_REQUEST[$week[$i].'open']."-".$_REQUEST[$week[$i].'close'].")";
                    }
                    
                }
            }
        }
        
        $dd = implode(",",$data);
        
        mysqli_query($con,"INSERT INTO `gametime_delhi`(`market`, `open`, `close`, `days`,`sort_no`) VALUES ('$market','$open','$close','$dd','$sort_no')");
        
        redirect("delhi-market-list.php");
        
    } else {
        $error = "Market Already Exists";
    }
    
    


}



?>

<style>
    .modal-dialog {
    max-width: 70% !important;
}
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Games</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Games</li>
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
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>Game Name</th>
                    <th>Close Time</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Rate</th>
                  </tr>
                  </thead>
                  <tbody>
                
                  
                  <?php
                    $i = 0;
                    $game = mysqli_query($con, "SELECT * FROM `gametime_delhi` ORDER BY sn DESC");
                    while($row = mysqli_fetch_array($game)){
                  ?>
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $row['market']; ?></td>
                    
                    <td>
                      <?php 
                        echo  date("h:i:sa", strtotime($row['close']));
                      ?>
                    </td>
                    <td>
                      <?php 
                        echo  $row['days'];
                      ?>
                    </td>
                    <td>
                      <?php
                        if($row['active'] == 0){
                      ?>
                        <a href="delhi-market-list.php?Active2=<?php echo $row['sn']; ?>" class="btn btn-sm btn-danger">No</a>
                      <?php
                        }else{
                      ?>
                        <a href="delhi-market-list.php?Deactive2=<?php echo $row['sn']; ?>" class="btn btn-sm btn-success">Yes</a>
                      <?php
                        }
                      ?>
                    </td>
                   
                    <td>
                      <a href="delhi-market-list.php?delete2=<?php echo $row['sn']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                      <td>
                      <a href="set_rate.php?market=<?php echo $row['market']; ?>" class="btn btn-danger btn-sm">Rates</a>
                    </td>
                  </tr>
                  <!-- Edit Game -->
                  <?php
                    $i++;
                   }
                    if(isset($_GET['delete2'])){
                        $gameID = $_GET['delete2'];
                      //  echo "delete from gametime_manual where sn='$gameID'";
                        $updateGame = mysqli_query($con, "delete from gametime_delhi where sn='$gameID'");
                        if($updateGame){
                            echo "<script>window.location.href='delhi-market-list.php';</script>";
                        }
                    }

                    // Active User Status
                    if(isset($_GET['Active2'])){
                        $id = $_GET['Active2'];
                        
                        $updateUser = mysqli_query($con, "UPDATE `gametime_delhi` SET `active`='1' WHERE `sn`='$id'");
                        if($updateUser){
                            echo "<script>window.location.href='delhi-market-list.php';</script>";
                        }
                        
                    }
                    
                    // Deactive User Status
                    if(isset($_GET['Deactive2'])){
                        $id = $_GET['Deactive2'];
                        
                        $updateUser = mysqli_query($con, "UPDATE `gametime_delhi` SET `active`='0' WHERE `sn`='$id'");
                        if($updateUser){
                            echo "<script>window.location.href='delhi-market-list.php';</script>";
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
    

<!-- Add New Game -->
<div class="modal fade" id="AddNewGame">
    <div class="modal-dialog">
        <div class="modal-content bg-primary">
            <div class="modal-header">
              <h4 class="modal-title">Add New Game</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    
                
                       
                    <div class="form-group">
                        <label>Enter Game Name</label>
                        <input type="text" class="form-control" name="gameName"  />
                    </div>
                    
                   
                    <div class="form-group">
                        <label>Close Time</label>
                        <input type="time" class="form-control" name="CloseTime" required />
                    </div>
                    
                    <div class="form-group">
                        <label>Does market have special timings ?</label>
                        <select id="type" name="type" class="form-control" onchange="time_check(this.value)">
                            
                                <option value='No'>No</option>
                                <option value='Yes'>Yes</option>
                           
                        </select>
                    </div>
                    
      
                    <script>
                        function time_check(value){
                            if(value == "Yes"){
                                $("#timings").show();
                               
                                var close = $('#close').val();
                                <?php for($i = 0;$i < count($week); $i++){ ?>
                                $("#<?php echo $week[$i]; ?>close").val(close);
                                <?php } ?>
                                
                            } else {
                                $("#timings").hide();
                            }
                        }
                    </script>
                    
                    
    
                    <div id="timings" class="timings" style="display:none;">
                        <h4>Timings</h4>
                        <div class="col-sm-12">
                            
                            <?php
                            
                       
                        
                        for($i = 0;$i < count($week); $i++){
                        
                        
                        ?>
                            
                            <div class="row" style="align-items: center;">
                                    <div class="col-sm-2">
                                    <span><?php echo $week[$i]; ?></span>
                                </div>
                                <div class="col-sm-2">
                                     <div class="form-group" style="    margin-bottom: 10px !important">
                                        <select class="form-control" name="<?php echo $week[$i]; ?>timetype" id="exampleFormControlSelect1" onchange="open_change(this.value,"<?php echo $week[$i]; ?>")">
                                            
                                            <option value='open'>Open</option>
                                            <option value='close'>Close</option>
                                        </select>
                                    </div>
                                </div>
                              
                                <div class="col-sm-4">
                                    <div class="form-group form-input">
                                        <input type="text" class="form-control"  placeholder="Close Time  (24h format)"  id="<?php echo $week[$i]; ?>close" name="<?php echo $week[$i]; ?>close">
                                    </div>
                                </div>
                            </div>
                           
                           <?php }?>
                           
                           
                        </div>
                        
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
    extract($_POST);
  $gameName = $_POST['gameName'];
   
  
  $CloseTime = $_POST['CloseTime'];
  $withdrawCloseTime24  = date("H:i", strtotime($CloseTime));
  
  $OpenTime = $CloseTime;
  $withdrawOpenTime24  = $withdrawCloseTime24;
  
  $CreateDate = date("Y-m-d H:i:s");
  
   if($type == "Yes"){
            for($i = 0; $i < count($week);$i++){
                if($_POST[$week[$i].'timetype'] == "close"){
                    $data[] = $week[$i]."(CLOSED)";
                } else if($_POST[$week[$i].'close'] != $close){
                    
                        if($_POST[$week[$i].'close'] != ""){
                        $data[] = $week[$i]."(".$_POST[$week[$i].'close']."-".$_POST[$week[$i].'close'].")";
                    }
                    
                }
            }
    }
    
    $dd = implode(",",$data);

    $insert =  mysqli_query($con,"INSERT INTO `gametime_delhi`(`market`, `open`, `close`, `days`,`sort_no`) VALUES ('$gameName','$withdrawOpenTime24','$withdrawCloseTime24','$dd','0')");
  //echo "INSERT INTO `gametime_manual`(`market`, `open`, `close`, `days`,`sort_no`) VALUES ('$gameName','$withdrawOpenTime24','$withdrawCloseTime24','$dd','')";
//   if($gameName == ""){
      
//     $insert =  mysqli_query($con,"INSERT INTO `gametime_manual`(`market`, `open`, `close`, `days`,`sort_no`) VALUES ('$gameName2','$withdrawOpenTime24','$withdrawCloseTime24','$dd','')");
//   // $insert =  mysqli_query($con,"INSERT INTO `gametime_new`(`market`, `open`, `close`, `days`,`sort_no`) VALUES ('$gameName2','$withdrawOpenTime24','$withdrawCloseTime24','$dd','')");
 
//   } else {
      
//     $insert =  mysqli_query($con,"INSERT INTO `gametime_manual`(`market`, `open`, `close`, `days`,`sort_no`) VALUES ('$gameName2','$withdrawOpenTime24','$withdrawCloseTime24','$dd','')");
//   }
      
      if($insert){
          echo "<script>window.location.href = 'delhi-market-list.php';</script>";
      }else{
          echo "<script>alert('Server Error. Please try again after some time!!');</script>";
      }
  
}

include('footer.php'); 
?>