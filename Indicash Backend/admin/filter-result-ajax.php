<?php
    $resultDate = date('d/m/Y',strtotime($_POST['resultDate']));
   // $resultDate = $_POST['resultDate'];
    
    include('config.php');
    
    if($resultDate != ''){
        $result = mysqli_query($con, "SELECT * FROM `manual_market_results` WHERE `date`='$resultDate' ORDER BY sn DESC");
            $i = 1;
            while($row = mysqli_fetch_array($result)){
                $gameID = $row['market'];
?>

<tr>
    <td><?php echo $i; ?></td>
    <td><?php echo $row['market']; ?></td>
    <td><?php echo $row['date']; ?></td>
    <td><?php echo $row['open_panna'].'-'.$row['open']; ?></td>
    <td><?php echo $row['close_panna'].'-'.$row['close']; ?></td>
    <td>
        <button class="btn btn-danger" data-toggle="modal" data-target="#deleteResult<?php echo $i; ?>">Delete</button>
    </td>
</tr>

<?php
            }
        $i++;
    }
    
    if(isset($_GET['resultDate'])){
        $resultDate = $_GET['resultDate'];
        $gameID = $_GET['gameID'];
                                                    
        //delete result
        $deleteResult = mysqli_query($con, "DELETE FROM `declare_result` WHERE `game_id`='$gameID' AND `result_date`='$resultDate'");
                                                    
        $winHistory = mysqli_query($con, "SELECT * FROM `winning_history` WHERE `game_id`='$gameID' AND `date`='$resultDate' ");
        while($winFetch = mysqli_fetch_array($winHistory)){
            $bidTX = $winFetch['bid_tx_id'];
                                                        
            $deleteWalletRecord = mysqli_query($con, "DELETE FROM `wallet` WHERE `transactionID`='$bidTX'");
            if($deleteWalletRecord){
                $DeleteWinHistory = mysqli_query($con, "DELETE FROM `winning_history` WHERE `game_id`='$gameID' AND `date`='$resultDate'");   

                if($DeleteWinHistory){
                    echo "<script>window.location.href='declare-result.php';</script>";
                }
            }
                                                        
        }
        echo "<script>window.location.href='declare-result.php';</script>";
    }
    
?>