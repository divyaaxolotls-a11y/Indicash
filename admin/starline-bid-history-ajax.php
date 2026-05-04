<?php
     $date = date('d/m/Y',strtotime($_POST['date']));
     $gameID = explode("_",$_POST['gameID']);
     $market = $gameID[1];
     $timing = $gameID[2];
     $gameType = $_POST['gameType'];

    include('config.php');
    $sql = "
        SELECT sg.*
        FROM starline_games sg
        JOIN starline_timings st ON sg.timing_sn = st.sn
        WHERE sg.date='$date'
        AND sg.bazar='$market'
        AND st.close='$timing'
    ";
    
    if(!empty($gameType)){
        $sql .= " AND sg.game='$gameType'";
    }

    $select = mysqli_query($con, $sql);
    
    // if($date != '' && $gameID != '' && $gameType != ''){
    //     // $select = mysqli_query($con, "SELECT * FROM `starline_games` WHERE `date`= '$date' AND `bazar`='$market' AND timing_sn='$timing' AND `game`='$gameType' ");    
    //   // echo "SELECT * FROM `starline_games` WHERE `date`= '$date' AND `bazar`='$market' AND timing_sn='$timing' AND `game`='$gameType' ";
    //   $select = mysqli_query($con, "
    //         SELECT sg.*
    //         FROM starline_games sg
    //         JOIN starline_timings st ON sg.timing_sn = st.sn
    //         WHERE sg.date='$date'
    //         AND sg.bazar='$market'
    //         AND st.close='$timing'
    //     ");
    // }elseif($date != '' && $gameID != '' && $gameType == ''){
    //     // $select = mysqli_query($con, "SELECT * FROM `starline_games` WHERE `date`= '$date' AND `bazar`='$market' AND timing_sn='$timing' ");  
    //   //  echo "SELECT * FROM `starline_games` WHERE `date`= '$date' AND `bazar`='$market' AND timing_sn='$timing' ";
    // }
           

    
    $i = 1;
    while($row = mysqli_fetch_array($select)){
        // user data
        $userID = $row['user'];
        $user = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$userID' ");
        $fetch = mysqli_fetch_array($user);
        
?>
    <tr>
        <td><?php echo $i; ?></td>
        <td><?php if($fetch['name'] != ''){ echo $fetch['name'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($fetch['mobile'] != ''){ echo $fetch['mobile'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['sn'] != ''){echo $row['sn'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['bazar'] != ''){echo $row['bazar'].' '.$row['timing_sn'];}else{ echo 'N/A'; } ?></td>
        <td style="text-transform: capitalize;"><?php if($row['game'] != ''){echo $row['game'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['number'] != ''){echo $row['number'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['amount'] != ''){echo $row['amount'];}else{ echo 'N/A'; } ?></td>
        <td><a class="btn btn-info disabled" href="starline-update-bid-history.php?id=<?php echo $row['id']; ?>" disabled>Edit</a></td>
    </tr>    
    

<?php
$i++;
    }
?>