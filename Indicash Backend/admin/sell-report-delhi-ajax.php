<?php include('config.php');
$date2 = $_REQUEST['resultDate'];

function invenDescSort($item1,$item2)
{
     if ($item1['amount'] == $item2['amount']) return 0;
    return ($item1['amount'] < $item2['amount']) ? 1 : -1;
}

$date = date('d/m/Y',strtotime($_REQUEST['resultDate']));
$market = str_replace(" ","_",$_REQUEST['gameID']); 


?>

               
               
       <div class="row" style="margin-top:30px;">
                      
                  <h4 style="game_title">Jodi Digit</h4>
                
               </div>
                    <div class="row">
                  <div class="container-fluid colls">
                      
                       <div class="row">
                        <div class="col-sm">
                            <p>Digit</p>
                            <p>Amount</p>
                        </div>
                        <?php 
                        
                        if(isset($digit)) { unset($digit); }
                        $get_data = mysqli_query($con,"SELECT * FROM `jodi_digit`");
                      while($data = mysqli_fetch_array($get_data)) { 
                      
                      $query = mysqli_query($con,"select sum(amount) as total from games where bazar='$market' AND number='".$data['number']."' AND date='$date'");
                      if(mysqli_num_rows($query) > 0){
                        $get_q = mysqli_fetch_array($query);
                        if($get_q['total'] == null){
                            $get_q['total'] = "0";
                        }
                        $fc['digit'] = $data['number'];
                        $fc['amount'] = $get_q['total'];
                        $digit[] = $fc;
                      } else {
                          $fc['digit'] = $data['number'];
                        $fc['amount'] = "0";
                        $digit[] = $fc;
                      }
                      
                      
                      }
                      
                      
                    usort($digit,'invenDescSort');
                      //$keys = array_keys($digit);
                        for($x = 0; $x < count($digit); $x++){
                        ?>
                        <div class="col-sm">
                          <p><?php echo $digit[$x]['digit']; ?></p>
                               <p ><span class="<?php if($digit[$x]['amount'] == "0" || $digit[$x]['amount'] == 0){ echo 'redbox'; } else { echo 'bluebox'; } ?>"><?php echo $digit[$x]['amount']; ?></span></p>
                        </div>
                       
                      <?php if(($x+1) % 10 == 0 && $x+1 < count($digit)){ ?>
                    </div>
                            <div class="row">
                        <div class="col-sm">
                            <p>Digit</p>
                            <p>Amount</p>
                        </div>
                       <?php } ?>
                       
                       <?php }  ?>
                      
                      </div>
                    </div>
               </div>



