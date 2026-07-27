<?php include('config.php');
$date2 = $_REQUEST['resultDate'];

function invenDescSort($item1,$item2)
{
     if ($item1['amount'] == $item2['amount']) return 0;
    return ($item1['amount'] < $item2['amount']) ? 1 : -1;
}

$date = date('d/m/Y',strtotime($_REQUEST['resultDate']));


$type = $_REQUEST['type'];
$market = $_REQUEST['gameID']; 

$makrs = explode('_',$market);
$market = $makrs[0];
$timing = $makrs[1];
$market2 = $market;


if($type == "all"){
    ?>
    
       <div class="row">
                      
                  <h4 style="game_title">Single Digit</h4>
                
               </div>
                    <div class="row">
                  <div class="container-fluid colls">
                      
                       <div class="row">
                        <div class="col-sm">
                            <p>Digit</p>
                            <p>Amount</p>
                        </div>
                        <?php $get_data = mysqli_query($con,"SELECT * FROM `single_digit`");
                      while($data = mysqli_fetch_array($get_data)) { 
                      
                      $query = mysqli_query($con,"select sum(amount) as total from starline_games where bazar='$market2' AND number='".$data['number']."' AND date='$date' AND timing_sn='$timing'");
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
                       <?php } ?>
                      </div>
                    </div>
               </div>
               
               
               
       <div class="row" style="margin-top:30px;">
                      
                  <h4 style="game_title">Panna Digit</h4>
                
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
                        $get_data = mysqli_query($con,"SELECT * FROM single_pana");
                      while($data = mysqli_fetch_array($get_data)) { 
                      
                      $query = mysqli_query($con,"select sum(amount) as total from starline_games where bazar='$market2' AND number='".$data['single_pana']."' AND date='$date' AND timing_sn='$timing'");
                      if(mysqli_num_rows($query) > 0){
                        $get_q = mysqli_fetch_array($query);
                        if($get_q['total'] == null){
                            $get_q['total'] = "0";
                        }
                        $fc['digit'] = $data['single_pana'];
                        $fc['amount'] = $get_q['total'];
                        $digit[] = $fc;
                      } else {
                          $fc['digit'] = $data['single_pana'];
                        $fc['amount'] = "0";
                        $digit[] = $fc;
                      }
                      
                      
                      }
                      
                       $get_data = mysqli_query($con,"SELECT * FROM double_pana");
                      while($data = mysqli_fetch_array($get_data)) { 
                      
                      $query = mysqli_query($con,"select sum(amount) as total from starline_games where bazar='$market2' AND number='".$data['double_pana']."' AND date='$date' AND timing_sn='$timing'");
                      if(mysqli_num_rows($query) > 0){
                        $get_q = mysqli_fetch_array($query);
                        if($get_q['total'] == null){
                            $get_q['total'] = "0";
                        }
                        $fc['digit'] = $data['double_pana'];
                        $fc['amount'] = $get_q['total'];
                        $digit[] = $fc;
                      } else {
                          $fc['digit'] = $data['double_pana'];
                        $fc['amount'] = "0";
                        $digit[] = $fc;
                      }
                      
                      
                      }
                      
                       $get_data = mysqli_query($con,"SELECT * FROM triple_pana");
                      while($data = mysqli_fetch_array($get_data)) { 
                      
                      $query = mysqli_query($con,"select sum(amount) as total from starline_games where bazar='$market2' AND number='".$data['triple_pana']."' AND date='$date' AND timing_sn='$timing'");
                      if(mysqli_num_rows($query) > 0){
                        $get_q = mysqli_fetch_array($query);
                        if($get_q['total'] == null){
                            $get_q['total'] = "0";
                        }
                        $fc['digit'] = $data['triple_pana'];
                        $fc['amount'] = $get_q['total'];
                        $digit[] = $fc;
                      } else {
                          $fc['digit'] = $data['triple_pana'];
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
    
<?php
} 
if($type == "single"){ ?>

       <div class="row">
                      
                  <h4 style="game_title">Single Digit</h4>
                
               </div>
                    <div class="row">
                  <div class="container-fluid colls">
                      
                       <div class="row">
                        <div class="col-sm">
                            <p>Digit</p>
                            <p>Amount</p>
                        </div>
                        <?php $get_data = mysqli_query($con,"SELECT * FROM `single_digit`");
                      while($data = mysqli_fetch_array($get_data)) { 
                      
                      $query = mysqli_query($con,"select sum(amount) as total from games where bazar='$market2' AND number='".$data['number']."' AND date='$date'");
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
                       <?php } ?>
                      </div>
                    </div>
               </div>
               
<?php } ?>



<?php

if($type == "panna"){ ?>
          
       <div class="row" style="margin-top:30px;">
                      
                  <h4 style="game_title">Panna Digit</h4>
                
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
                        $get_data = mysqli_query($con,"SELECT * FROM single_pana");
                      while($data = mysqli_fetch_array($get_data)) { 
                      
                      $query = mysqli_query($con,"select sum(amount) as total from starline_games where bazar='$market2' AND number='".$data['single_pana']."' AND date='$date' AND timing_sn='$timing'");
                      if(mysqli_num_rows($query) > 0){
                        $get_q = mysqli_fetch_array($query);
                        if($get_q['total'] == null){
                            $get_q['total'] = "0";
                        }
                        $fc['digit'] = $data['single_pana'];
                        $fc['amount'] = $get_q['total'];
                        $digit[] = $fc;
                      } else {
                          $fc['digit'] = $data['single_pana'];
                        $fc['amount'] = "0";
                        $digit[] = $fc;
                      }
                      
                      
                      }
                      
                       $get_data = mysqli_query($con,"SELECT * FROM double_pana");
                      while($data = mysqli_fetch_array($get_data)) { 
                      
                      $query = mysqli_query($con,"select sum(amount) as total from starline_games where bazar='$market2' AND number='".$data['double_pana']."' AND date='$date' AND timing_sn='$timing'");
                      if(mysqli_num_rows($query) > 0){
                        $get_q = mysqli_fetch_array($query);
                        if($get_q['total'] == null){
                            $get_q['total'] = "0";
                        }
                        $fc['digit'] = $data['double_pana'];
                        $fc['amount'] = $get_q['total'];
                        $digit[] = $fc;
                      } else {
                          $fc['digit'] = $data['double_pana'];
                        $fc['amount'] = "0";
                        $digit[] = $fc;
                      }
                      
                      
                      }
                      
                       $get_data = mysqli_query($con,"SELECT * FROM triple_pana");
                      while($data = mysqli_fetch_array($get_data)) { 
                      
                      $query = mysqli_query($con,"select sum(amount) as total from starline_games where bazar='$market2' AND number='".$data['triple_pana']."' AND date='$date' AND timing_sn='$timing'");
                      if(mysqli_num_rows($query) > 0){
                        $get_q = mysqli_fetch_array($query);
                        if($get_q['total'] == null){
                            $get_q['total'] = "0";
                        }
                        $fc['digit'] = $data['triple_pana'];
                        $fc['amount'] = $get_q['total'];
                        $digit[] = $fc;
                      } else {
                          $fc['digit'] = $data['triple_pana'];
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
<?php } ?>

