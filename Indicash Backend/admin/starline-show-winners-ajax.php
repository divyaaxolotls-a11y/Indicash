<?php
     $date = date('d/m/Y',strtotime($_REQUEST['date']));
     $gameID = explode("_",$_REQUEST['gameID']);
     $market = $gameID[1];
     $timing = $gameID[2];
    $digit = $_REQUEST['digit'];
    $panna = $_REQUEST['panna'];

    include('config.php');
    // Get timing_sn properly
$timing_q = mysqli_query($con,
    "SELECT sn FROM starline_timings 
     WHERE name='$market' 
     AND close='$timing' 
     LIMIT 1"
);

if(mysqli_num_rows($timing_q) == 0){
    die("Timing not found");
}

$timing_row = mysqli_fetch_array($timing_q);
$timing_sn = $timing_row['sn'];
$get_rates = mysqli_fetch_array(mysqli_query($con,"SELECT * FROM `rate`"));
        $select = mysqli_query($con, "SELECT * FROM `starline_games` WHERE `date`= '$date' AND `bazar`='$market' AND timing_sn='$timing_sn'  AND (number='$digit' OR number='$panna') ");  
         //  echo "SELECT * FROM `starline_games` WHERE `date`= '$date' AND `bazar`='$market' AND timing_sn='$timing' AND (number='$digit' OR number='$panna') ";

    
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
        <td><?php if($row['amount'] != ''){echo $row['amount'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['amount'] != ''){echo $get_rates[$row['game']]*$row['amount'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['bazar'] != ''){echo $row['bazar'].' '.$row['timing_sn'];}else{ echo 'N/A'; } ?></td>
        <td style="text-transform: capitalize;"><?php if($row['game'] != ''){echo $row['game'];}else{ echo 'N/A'; } ?></td>
        <td><?php if($row['number'] != ''){echo $row['number'];}else{ echo 'N/A'; } ?></td>
                                <td><?php echo date('h:i A d-m-Y',$row['created_at']); ?></td>
       <td>
                                <a href="user-profile.php?userID=<?php echo $row['user']; ?>"><i class="fas fa-eye" style="font-size:25px;"></i></a>
                              </td>
      
    </tr>    
    

<?php
$i++;
    }
?>