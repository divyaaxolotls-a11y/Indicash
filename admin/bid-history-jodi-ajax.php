<?php
     $date = date('d/m/Y',strtotime($_POST['date']));
     $gameID = $_POST['gameID'];
     $gameType = $_POST['gameType'];
     
    $market = str_replace(" ","_",$gameID);
    $market_1 = str_replace(" ","_",$gameID.' OPEN');
    $market_2 = str_replace(" ","_",$gameID.' CLOSE');

    include('config.php');
    if($date != '' && $gameID != '' && $gameType != ''){
        $select = mysqli_query($con, "SELECT * FROM `games` WHERE `date`= '$date' AND (`bazar`='$market' OR `bazar`='$market_1' OR `bazar`='$market_2' ) AND `game`='$gameType' "); 
    }elseif($date != '' && $gameID != '' && $gameType == ''){
        $select = mysqli_query($con, "SELECT * FROM `games` WHERE `date`= '$date' AND (`bazar`='$market' OR `bazar`='$market_1' OR `bazar`='$market_2' ) ");  
    }
           

    
    $i = 1;
    while($row = mysqli_fetch_array($select)){
        // user data
        $userID = $row['user'];
        $user = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$userID' ");
        $fetch = mysqli_fetch_array($user);
        
        $game_id = $row['bazar'];
?>
    <tr>
        <td><?php echo $i; ?></td>
        <td><?php if($fetch['name'] != ''){ echo $fetch['name'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($fetch['mobile'] != ''){ echo $fetch['mobile'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['sn'] != ''){echo $row['sn'];}else{ echo 'N/A'; } ?></td>
        <td><?php echo $game_id; ?></td>
        <td style="text-transform: capitalize;"><?php if($row['game'] != ''){echo $row['game'];}else{ echo 'N/A'; } ?></td>
        <td style="text-transform: capitalize;"><?php if('' != ''){echo $row['session'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['number'] != ''){echo $row['number'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['amount'] != ''){echo $row['amount'];}else{ echo 'N/A'; } ?></td>
        <td><a class="btn btn-info" href="update-bid-history.php?id=<?php echo $row['sn']; ?>">Edit</a></td>
    </tr>    
    

<?php
$i++;
    }
?>