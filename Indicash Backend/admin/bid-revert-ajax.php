<table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>User Mobile</th>
                    <th>Bid Points</th>
                    <th>Bid Number</th>
                  </tr>
                  </thead>
                  <tbody id="tbody">

<?php 
     $date = date('d/m/Y',strtotime($_POST['date']));
    $gameID = $_POST['gameID'];
    
    include('config.php');
    
    
    
    
     
    $market = str_replace(" ","_",$gameID);
    $market_1 = str_replace(" ","_",$gameID.' OPEN');
    $market_2 = str_replace(" ","_",$gameID.' CLOSE');
    
    
$num_results_on_page = 10;  
if (isset($_GET["page"])) {
	$page  = $_GET["page"]; 
	} 
	else{ 
	$page=1;
	};  
$start_from = ($page-1) * $num_results_on_page;  

$search_url_add = "";

$result = mysqli_query($con,"SELECT * FROM `games` WHERE `date`='$date' AND (`bazar`='$market' OR `bazar`='$market_1' OR `bazar`='$market_2' )  LIMIT $start_from, $num_results_on_page");
    
$result_db = mysqli_query($con,"SELECT COUNT(sn) FROM `games` WHERE `date`='$date' AND (`bazar`='$market' OR `bazar`='$market_1' OR `bazar`='$market_2' ) "); 

$action_url = "&page=".$page.$search_url_add;


$row_db = mysqli_fetch_row($result_db);  
$total_pages = $row_db[0];  

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;


     
    $i = 1;
    
    while($row = mysqli_fetch_array($result)){
        $userID = $row['user'];
        $users = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$userID' ");
        $userFetch = mysqli_fetch_array($users);
        

?>

<tr>
    <td><?php echo $i; ?></td>
    <td><?php echo $userFetch['name']; ?></td>
    <td><?php echo $userFetch['mobile']; ?></td>
    <td><?php echo $row['amount']; ?></td>
    <td><?php echo $row['number']; ?></td>
</tr>

<?php
$i++;
}
?>

 </tbody>
</table>

  <?php if (ceil($total_pages / $num_results_on_page) > 0): 
                                    
                     ?>
<ul class="pagination">
  <?php if ($page > 1): ?>
  <li class="prev page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page-1 ?><?php echo $search_url_add; ?>">Prev</a></li>
  <?php endif; ?>

  <?php if ($page > 3): ?>
  <li class="start page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=1<?php echo $search_url_add; ?>">1</a></li>
  <li class="dots page-item">...</li>
  <?php endif; ?>

  <?php if ($page-2 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page-2 ?><?php echo $search_url_add; ?>"><?php echo $page-2 ?></a></li><?php endif; ?>
  <?php if ($page-1 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page-1 ?><?php echo $search_url_add; ?>"><?php echo $page-1 ?></a></li><?php endif; ?>

  <li class="currentpage page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page ?><?php echo $search_url_add; ?>"><?php echo $page ?></a></li>

  <?php if ($page+1 < ceil($total_pages / $num_results_on_page)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page+1 ?><?php echo $search_url_add; ?>"><?php echo $page+1 ?></a></li><?php endif; ?>
  <?php if ($page+2 < ceil($total_pages / $num_results_on_page)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page+2 ?><?php echo $search_url_add; ?>"><?php echo $page+2 ?></a></li><?php endif; ?>

  <?php if ($page < ceil($total_pages / $num_results_on_page)-2): ?>
  <li class="dots page-item">...</li>
  <li class="end page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo ceil($total_pages / $num_results_on_page) ?><?php echo $search_url_add; ?>"><?php echo ceil($total_pages / $num_results_on_page) ?></a></li>
  <?php endif; ?>

  <?php if ($page < ceil($total_pages / $num_results_on_page)): ?>
  <li class="next page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page+1 ?><?php echo $search_url_add; ?>">Next</a></li>
  <?php endif; ?>
</ul>
<?php endif; ?>