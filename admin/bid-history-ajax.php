  <table id="example1" class="w-100 table table-bordered table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>User Name</th>
      <th>Mobile Number</th>
      <th>Bid TXID</th>
      <th>Game Name</th>
      <th>Game Type</th>
      <th>Number</th>
      <th>Points</th>
      <th>Time</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody id="tbody">

<?php
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
    // Alert the user and redirect back to transaction.php
    echo "<script>
        alert('Invalid CSRF token. Please refresh the page and try again.');
        window.location.href = 'bid-history.php';
    </script>";
    exit; // Stop further execution
}


$date_str = strtotime($_POST['date']);
$date = date('d/m/Y', $date_str);
$gameID = $_POST['gameID'];
$gameType = $_POST['gameType'];

$chec_date = strtotime('-29 days');

$num_results_on_page = 10;
$page = isset($_GET["page"]) ? $_GET["page"] : 1;
$start_from = ($page - 1) * $num_results_on_page;

$search_url_add = "";

if ($chec_date < $date_str) {
    $table_name = "games";
} else {
    $table_name = "games_archive";
}

$market = str_replace(" ", "_", $gameID);
$market_1 = str_replace(" ", "_", $gameID . ' OPEN');
$market_2 = str_replace(" ", "_", $gameID . ' CLOSE');

include('config.php');

// Fetch total count for pagination
if ($date != '' && $gameID != '') {
    $count_query = "SELECT COUNT(*) FROM $table_name WHERE date= '$date' AND (bazar='$market' OR bazar='$market_1' OR bazar='$market_2')";
    $result_db = mysqli_query($con, $count_query);

    if (!$result_db) {
        echo "Error in count query: " . mysqli_error($con);
    } else {
        $row_db = mysqli_fetch_row($result_db);
        $total_pages = $row_db[0];
    }
}

// Fetch game data
if ($date != '' && $gameID != '' && $gameType != '') {
    $select_query = "SELECT * FROM $table_name WHERE date= '$date' AND (bazar='$market' OR bazar='$market_1' OR bazar='$market_2') AND game='$gameType'";
} elseif ($date != '' && $gameID != '' && $gameType == '') {
    $select_query = "SELECT * FROM $table_name WHERE date= '$date' AND (bazar='$market' OR bazar='$market_1' OR bazar='$market_2')";
}

$select = mysqli_query($con, $select_query);

if (!$select) {
    echo "Error in select query: " . mysqli_error($con);
} else {
    $i = (($page - 1) * 10) + 1;
    while ($row = mysqli_fetch_array($select)) {
        $userID = $row['user'];
        $user_query = "SELECT * FROM users WHERE mobile='$userID'";
        $user = mysqli_query($con, $user_query);
        $fetch = mysqli_fetch_array($user);
        $game_id = $row['bazar'];
?>
    <tr>
        <td><?php echo $i; ?></td>
        <td><?php echo $fetch['name'] != '' ? $fetch['name'] : 'N/A'; ?></td>
        <td><?php echo $fetch['mobile'] != '' ? $fetch['mobile'] : 'N/A'; ?></td>
        <td><?php echo $row['sn'] != '' ? $row['sn'] : 'N/A'; ?></td>
        <td><?php echo $game_id; ?></td>
        <td style="text-transform: capitalize;"><?php echo $row['game'] != '' ? $row['game'] : 'N/A'; ?></td>
        <td><?php echo $row['number']; ?></td>
        <td><?php echo $row['amount'] != '' ? $row['amount'] : 'N/A'; ?></td>
        <td><?php echo $row['timestamp'] != '' ? $row['timestamp'] : 'N/A'; ?></td>
        <td><a class="btn btn-info" href="update-bid-history.php?id=<?php echo $row['sn']; ?>">Edit</a></td>
    </tr>
<?php
        $i++;
    }
}
?>
  </tbody>
</table>
