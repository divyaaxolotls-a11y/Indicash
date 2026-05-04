<?php 
   $gameID = $_POST['gameID'];
    $currDate = date('d/m/Y');
    

    
    include('config.php');
     $gameID;
    if($gameID == "0"){
        
        $BidHis = mysqli_query($con, "SELECT SUM(amount) AS total_points FROM `games` WHERE `date`='$currDate' "); 
      //  echo "SELECT SUM(amount) AS total_points FROM `games` WHERE `date`='$currDate' ";
        $rowBid = mysqli_fetch_array($BidHis); 
        
        $total = $rowBid['total_points'];
        if($total == 0){
            echo 0;
        }else{
            echo $total;
        }
    }
    else{
            
        
        $mrk1 = str_replace(" ","_",$gameID);
        $mrk2 = str_replace(" ","_",$gameID.' OPEN');
        $mrk3 = str_replace(" ","_",$gameID.' CLOSE');
        
        $BidHis = mysqli_query($con, "SELECT SUM(amount) AS total_points FROM `games` WHERE `date`='$currDate' AND (bazar='$mrk1' OR bazar='$mrk2' OR bazar='$mrk3') "); 
       // echo"SELECT SUM(amount) AS total_points FROM `games` WHERE `date`='$currDate' AND (bazar='$mrk1' OR bazar='$mrk2' OR bazar='$mrk3') ";
        $rowBid = mysqli_fetch_array($BidHis); 
        
        $total = $rowBid['total_points'];
        if($total == 0){
            echo 0;
        }else{
            echo $total;
        }
    }
    
?>