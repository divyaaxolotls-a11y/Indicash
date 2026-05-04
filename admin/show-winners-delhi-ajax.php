<?php include('config.php');
$date2 = $_REQUEST['date'];

$date = date('d/m/Y',strtotime($_REQUEST['resultDate']));


$jodi = $_REQUEST['jodi'];
$market = $_REQUEST['gameID'];

$get_rates = mysqli_fetch_array(mysqli_query($con,"SELECT * FROM `rate`"));

  $open = $jodi[0];
  $close = $jodi[1];

        $bazar = str_replace(" ","_",$market);
        $bazar2 = str_replace(" ","_",$market.' OPEN');
        $bazar3 = str_replace(" ","_",$market.' CLOSE');
        
 
  
  $qry = "SELECT * FROM games WHERE ( ( bazar='$bazar2' AND number='$open' ) OR ( bazar='$bazar' AND number='$jodi' ) OR ( bazar='$bazar3' AND number='$close' ) ) AND date='$date'";
  

?>

<?php
                        $winning = mysqli_query($con, $qry);
                        $i = 1;
                        while($row = mysqli_fetch_array($winning)){
                            $userID = $row['user'];
                            $user = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$userID' ");
                            $fetch = mysqli_fetch_array($user);
                                                    
                    ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $fetch['name']; ?></td>
                                <td><?php echo $row['amount']; ?></td>
                                <td><?php echo $get_rates[$row['game']]*$row['amount']; ?></td>
                                <td><?php echo $row['bazar']; ?></td>
                                <td><?php echo $row['game']; ?></td>
                                <td><?php echo $row['number']; ?></td>
                                <td><?php echo date('h:i A d-m-Y',$row['created_at']); ?></td>
                               <td>
                                <a href="user-profile.php?userID=<?php echo $row['user']; ?>"><i class="fas fa-eye" style="font-size:25px;"></i></a>
                              </td>
                            </tr>
                    <?php
                            $i++;
                        }
                    ?>