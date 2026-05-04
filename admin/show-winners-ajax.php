<?php 
include('config.php');
session_start();

// CSRF Token Check
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
    // Alert the user and redirect back to declare-result.php
    echo "<script>
        alert('Invalid CSRF token. Please refresh the page and try again.');
        window.location.href = 'declare-result.php';
    </script>";
    exit; // Stop further execution
}

// Sanitize inputs
$date = isset($_REQUEST['date']) ? htmlspecialchars($_REQUEST['date'], ENT_QUOTES, 'UTF-8') : '';
$digit = isset($_REQUEST['digit']) ? htmlspecialchars($_REQUEST['digit'], ENT_QUOTES, 'UTF-8') : '';
$panna = isset($_REQUEST['panna']) ? htmlspecialchars($_REQUEST['panna'], ENT_QUOTES, 'UTF-8') : '';
$session = isset($_REQUEST['session']) ? htmlspecialchars($_REQUEST['session'], ENT_QUOTES, 'UTF-8') : '';
$market = isset($_REQUEST['market']) ? htmlspecialchars($_REQUEST['market'], ENT_QUOTES, 'UTF-8') : '';

// Format date
$date2 = $_REQUEST['date'];
$date = date('d/m/Y', strtotime($date2));

// Fetch rates from the database
$get_rates = mysqli_fetch_array(mysqli_query($con,"SELECT * FROM `rate`"));

// Prepare the query based on the session
if($session == 'open') {
    // Prepare market for open session
    $mrk = str_replace(' ', '_', $market.' OPEN');
    $qry = "SELECT * FROM games WHERE bazar='$mrk' AND (number='$digit' OR number='$panna') AND date='$date'";
} else {
    // Query for close session
    $chk_if_query = mysqli_query($con, "SELECT * FROM manual_market_results WHERE market='$market' AND date='$date'");
    $chk_if_updated = mysqli_fetch_array($chk_if_query);

    $open = $chk_if_updated['open'];
    $opanna = $chk_if_updated['open_panna'];

    $mrk = str_replace(' ', '_', $market.' CLOSE');
    $mrk2 = str_replace(' ', '_', $market);

    $jodi = $open.$digit;
    $half1 = $opanna.'-'.$digit;
    $half2 = $panna.'-'.$open;
    $full = $opanna.'-'.$panna;

    $qry = "SELECT * FROM games WHERE (bazar='$mrk' OR bazar='$mrk2') AND (number='$digit' OR number='$panna' OR number='$jodi' OR number='$half1' OR number='$half2' OR number='$full') AND date='$date'";
}

?>

<?php
$winning = mysqli_query($con, $qry);
$i = 1;
while($row = mysqli_fetch_array($winning)) {
    $userID = $row['user'];
    $user = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$userID' ");
    $fetch = mysqli_fetch_array($user);
?>

<tr>
    <td><?php echo htmlspecialchars($i); ?></td>
    <td><?php echo htmlspecialchars($fetch['name']); ?></td>
    <td><?php echo htmlspecialchars($row['amount']); ?></td>
    <?php if($row['game_type'] == 'Sp' || $row['game_type'] == 'Dp') { ?>
        <td><?php echo htmlspecialchars($get_rates[$row['game_type']] * $row['amount']); ?></td>
    <?php } elseif(in_array($row['game_type'], ['round', 'centerpanna', 'aki', 'beki', 'chart50', 'chart60', 'chart70', 'akibekicut30', 'abr30pana', 'startend', 'cyclepana', 'groupjodi', 'panelgroup', 'bulkjodi', 'bulksp', 'bulkdp'])) { ?>
        <td><?php echo htmlspecialchars($get_rates[$row['game_type']] * $row['amount']); ?></td>
    <?php } else { ?>
        <td><?php echo htmlspecialchars($get_rates[$row['game']] * $row['amount']); ?></td>
    <?php } ?>
    <td><?php echo htmlspecialchars($row['bazar']); ?></td>
    <td><?php echo htmlspecialchars($row['game']); ?></td>
    <td><?php echo htmlspecialchars($row['game_type']); ?></td>
    <td><?php echo htmlspecialchars($row['number']); ?></td>
    <td><?php echo date('h:i A d-m', $row['created_at']); ?></td>
    <td>
        <a href="user-profile.php?userID=<?php echo htmlspecialchars($row['user']); ?>"><i class="fas fa-eye" style="font-size:25px;"></i></a>
    </td>
</tr>

<?php
    $i++;
}
?>

