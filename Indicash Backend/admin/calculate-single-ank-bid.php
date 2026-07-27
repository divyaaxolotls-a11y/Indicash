<?php 
    $gameID = $_POST['gameID'];
    $session = $_POST['session'];
    
    $currDate = date('d/m/Y');
  
    include('config.php');
    
    
    $singleAnk = mysqli_query($con, "SELECT * FROM single_digit ORDER BY number ASC");
    
    if (!$singleAnk) {
        die('Error in single_digit query: ' . mysqli_error($con));
    }
    
    while($row = mysqli_fetch_array($singleAnk)){
    
    $digit = $row['number'];
    if($session != ''){
    
    // id
?>

<div class="col mb-4">
    <div class="box" style="box-shadow: 0px 1px 10px #ccc; border-radius: 10px; padding:5px;">
        <p class="text-center"><b>Total Bids 
            <?php
                if($session == 'open'){
                    
                    $mrk2 = str_replace(" ","_",$gameID.' OPEN');
                    $BidHistory = mysqli_query($con, "SELECT * FROM games WHERE game='single' AND date='$currDate' AND bazar='$mrk2' AND number='$digit' ");
                }elseif($session == 'close'){
                    $mrk2 = str_replace(" ","_",$gameID.' CLOSE');
                    $BidHistory = mysqli_query($con, "SELECT * FROM games WHERE game='single' AND date='$currDate' AND bazar='$mrk2' AND number='$digit' ");
                }
                
                if (!$BidHistory) {
                    die('Error in BidHistory query: ' . mysqli_error($con));
                }
                
                $count = mysqli_num_rows($BidHistory);
                echo $count;
            ?>
        </b></p>
        <h3 class="text-center">
            <?php
                if($session == 'open'){
                    
                    $mrk2 = str_replace(" ","_",$gameID.' OPEN');
                    $BidHistoryTotal = mysqli_query($con, "SELECT SUM(amount) as TotalPoints FROM games WHERE game='single' AND date='$currDate' AND bazar='$mrk2' AND number='$digit' ");
                }elseif($session == 'close'){
                    $mrk2 = str_replace(" ","_",$gameID.' CLOSE');
                    $BidHistoryTotal = mysqli_query($con, "SELECT SUM(amount) as TotalPoints FROM games WHERE game='single' AND date='$currDate' AND bazar='$mrk2' AND number='$digit' ");
                }
                
                if (!$BidHistoryTotal) {
                    die('Error in BidHistoryTotal query: ' . mysqli_error($con));
                }
                
                $fetch = mysqli_fetch_array($BidHistoryTotal);
                
                if($fetch['TotalPoints'] == 0){
                    echo "0";
                }else{
                    echo $fetch['TotalPoints'];
                }
                
            ?>
        </h3>
        <p class="text-center">Total Bid Amount</p>
        <h6 class="bg-primary text-center text-light">Ank <?php echo $digit; ?></h6>
    </div>
</div>

<?php
    }}
?>
