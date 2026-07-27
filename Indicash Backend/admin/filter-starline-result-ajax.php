<?php
    $resultDate = date('d/m/Y',strtotime($_POST['resultDate']));
    
    include('config.php');
    
    $result = mysqli_query($con, "SELECT * FROM `starline_results` WHERE `date`='$resultDate' ORDER BY sn DESC");
        $i = 1;
        while($row = mysqli_fetch_array($result)){
    
    if($resultDate != ''){

?>

<tr>
    <td><?php echo $i; ?></td>
    <td><?php  echo $row['market'].' '.$row['timing']; ?></td>
    <td><?php echo $row['date']; ?></td>
    <td><span class="badge badge-danger"><?php echo $row['panna'].' - '.$row['number']; ?></span></td>
   <td>
       <button class="btn btn-danger" data-toggle="modal" data-target="#deleteResult<?php echo $i; ?>">Delete</button>
     
     <div class="modal fade" id="deleteResult<?php echo $i; ?>">
                                                <div class="modal-dialog modal-dialog-centered">
                                                  <div class="modal-content">
                                                  
                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                      <h4 class="modal-title">Are you sure you want to delete this result?</h4>
                                                    </div>
                                                    
                                                    <!-- Modal body -->
                                                    <div class="modal-body">
                                                      <form method="post">
                                                          <input type="hidden" name="resultDate" value="<?php echo $row['date']; ?>" required/>
                                                          <input type="hidden" name="gameID" value="<?php echo $row['market'].'~'.$row['timing']; ?>" required/>
                                                          <!-- Modal footer -->
                                                            <div class="modal-footer">
                                                              <button type="button" class="btn btn-info" data-dismiss="modal">No</button>
                                                              <button type="submit" name="deleteResult" class="btn btn-success">Yes</button>
                                                            </div>
                                                      </form>
                                                    </div>
                                                    
                                                    
                                                    
                                                  </div>
                                                </div>
                                              </div>
    </td>
  
   
</tr>

<?php
            }
        $i++;
    }
    
 if(isset($_POST['deleteResult'])){
   $resultDate = $_POST['resultDate'];
   $gameID = $_POST['gameID'];


   $exGam = explode("~",$gameID);
   $mrk = $exGam[0];
   $timing = $exGam[1];

   //delete result
   $deleteResult = mysqli_query($con,  "DELETE FROM `starline_results` WHERE `market`='$mrk' AND `date`='$resultDate' AND timing='$timing'");


   mysqli_query($con," update starline_games set status='0', is_loss='0' where date='$resultDate' AND bazar='$mrk' AND timing_sn='$timing'");


   $winHistory = mysqli_query($con,  "SELECT * FROM `manual_batch` WHERE `market`='$gameID' AND `date`='$resultDate' AND revert='0'");
   while($winFetch = mysqli_fetch_array($winHistory)){
     $bidTX = $winFetch['batch_id'];
     $sn = $winFetch['sn'];

     mysqli_query($con,"update manual_batch set revert='1' where sn='$sn'");

     $result = mysqli_query($con,  "SELECT * FROM `transactions` WHERE `batch_id`='$bidTX'");
     while($row = mysqli_fetch_array($result)){

       $game_id = $row['game_id'];
       $user = $row['user'];
       $amount = $row['amount'];


       mysqli_query($con, "update users set wallet=wallet-'$amount' where mobile='$user'");
     }

     mysqli_query($con, "delete from transactions where batch_id='$bidTX'");               
   }

   echo "<script>window.location.href='declare-result.php';</script>";
 }
    
?>