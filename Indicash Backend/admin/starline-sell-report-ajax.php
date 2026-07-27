<?php
    $ResultDate = $_POST['ResultDate'];
    $gameID = $_POST['gameID'];
    
    include('config.php');
     
?>
    <h5 class="card-title m-b-0 text-center text-info">Single Digit</h5>
    <br>
    <hr>
    <div class="row" style="width:100%">
        <div class="col-sm-2" style="width:8.33%;float:left;">
            <h5 class="digit">Digit</h5>
            <h5 class="points">Points</h5>
        </div>
<?php
    $singleAnk = mysqli_query($con, "SELECT *FROM `single_digit`");
    
    while($row = mysqli_fetch_array($singleAnk)){
        $digit = $row['number'];
?>                   
        <div class="col-sm-1" style="width:8.33%;float:left;">
            <h5 class="digit"><?php echo $digit; ?></h5>
            <h5 class="points">
                <?php
                    $bidHistory = mysqli_query($con, "SELECT SUM(points) AS totalPoints FROM `starline_bid_history` WHERE `game_type`='single digit' AND `open_digit`='$digit' AND `game_id`='$gameID' AND `date`='$ResultDate' ");
                    $fetch = mysqli_fetch_array($bidHistory);
                    $points = $fetch['totalPoints'];
                    if($points != ''){
                        echo '<span class="badge badge-primary">'.$points.'</span>';
                    }else{
                        echo '<span class="badge badge-danger">0</span>';
                    }
                ?>
            </h5>
        </div>
    
<?php

    }
    
?>
</div>

<!--Single Pana-->
<hr>
<br>
<h5 class="card-title m-b-0 text-center text-info">Single Pana</h5>
    <br>
    <hr>
    <div class="row" style="width:100%">
        <div class="col-sm-2" style="width:8.33%;float:left;">
            <h5 class="digit">Pana</h5>
            <h5 class="points">Points</h5>
        </div>
            <?php
                $singleAnk = mysqli_query($con, "SELECT *FROM `single_pana` ORDER BY single_pana ASC");
                
                while($row = mysqli_fetch_array($singleAnk)){
                    $pana = $row['single_pana'];
            ?> 
            
            <div class="col-sm-1" style="width:8.33%;float:left;">
                <h5 class="digit"><?php echo $pana; ?></h5>
                <h5 class="points">
                    <?php
                        $bidHistory = mysqli_query($con, "SELECT SUM(points) AS totalPoints FROM `starline_bid_history` WHERE `game_type`='single pana' AND `open_pana`='$pana' AND `game_id`='$gameID' AND `date`='$ResultDate' ");
                        $fetch = mysqli_fetch_array($bidHistory);
                        $points = $fetch['totalPoints'];
                        if($points != ''){
                            echo '<span class="badge badge-primary">'.$points.'</span>';
                        }else{
                            echo '<span class="badge badge-danger">0</span>';
                        }
                    ?>
                </h5>
            </div>
            
            <?php
                }
            ?>
    </div>

<!--Double Pana-->

<hr>
<br>
<h5 class="card-title m-b-0 text-center text-info">Double Pana</h5>
    <br>
    <hr>
    <div class="row" style="width:100%">
        <div class="col-sm-2" style="width:8.33%;float:left;">
            <h5 class="digit">Pana</h5>
            <h5 class="points">Points</h5>
        </div>
            <?php
                $singleAnk = mysqli_query($con, "SELECT *FROM `double_pana` ORDER BY double_pana ASC");
                
                while($row = mysqli_fetch_array($singleAnk)){
                    $pana = $row['double_pana'];
            ?> 
            
            <div class="col-sm-1" style="width:8.33%;float:left;">
                <h5 class="digit"><?php echo $pana; ?></h5>
                <h5 class="points">
                    <?php
                        $bidHistory = mysqli_query($con, "SELECT SUM(points) AS totalPoints FROM `starline_bid_history` WHERE `game_type`='double pana' AND `open_pana`='$pana' AND `game_id`='$gameID' AND `date`='$ResultDate' ");
                        $fetch = mysqli_fetch_array($bidHistory);
                        $points = $fetch['totalPoints'];
                        if($points != ''){
                            echo '<span class="badge badge-primary">'.$points.'</span>';
                        }else{
                            echo '<span class="badge badge-danger">0</span>';
                        }
                    ?>
                </h5>
            </div>
            
            <?php
                }
            ?>
    </div>
    
<!--Triple Pana-->
<hr>
<br>
<h5 class="card-title m-b-0 text-center text-info">Triple Pana</h5>
    <br>
    <hr>
    <div class="row" style="width:100%">
        <div class="col-sm-2" style="width:8.33%;float:left;">
            <h5 class="digit">Pana</h5>
            <h5 class="points">Points</h5>
        </div>
            <?php
                $singleAnk = mysqli_query($con, "SELECT *FROM `triple_pana` ORDER BY triple_pana ASC");
                
                while($row = mysqli_fetch_array($singleAnk)){
                    $pana = $row['triple_pana'];
            ?> 
            
            <div class="col-sm-1" style="width:8.33%;float:left;">
                <h5 class="digit"><?php echo $pana; ?></h5>
                <h5 class="points">
                    <?php
                        $bidHistory = mysqli_query($con, "SELECT SUM(points) AS totalPoints FROM `starline_bid_history` WHERE `game_type`='triple pana' AND `open_pana`='$pana' AND `game_id`='$gameID' AND `date`='$ResultDate' ");
                        $fetch = mysqli_fetch_array($bidHistory);
                        $points = $fetch['totalPoints'];
                        if($points != ''){
                            echo '<span class="badge badge-primary">'.$points.'</span>';
                        }else{
                            echo '<span class="badge badge-danger">0</span>';
                        }
                    ?>
                </h5>
            </div>
            
            <?php
                }
            ?>
    </div>