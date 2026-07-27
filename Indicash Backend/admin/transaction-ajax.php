<?php
include('config.php');

/* ===============================
   SESSION + CSRF
================================ */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    exit('Invalid Token');
}


/* ===============================
   RECEIVE FILTER DATA
================================ */

$date        = $_POST['date'] ?? '';
$user_mobile = $_POST['user_mobile'] ?? '';
$filter_type = strtolower($_POST['filter_type'] ?? '');
$game_name   = $_POST['game_name'] ?? '';
$session     = $_POST['session'] ?? '';


/* ===============================
   BUILD QUERY
================================ */

$where = " WHERE 1=1 ";

/* DATE FILTER */

if(!empty($date)){
    $search_date = date('d/m/Y', strtotime($date));
    $where .= " AND `date`='$search_date'";
}

/* USER FILTER */

if(!empty($user_mobile)){
    $safe_mobile = mysqli_real_escape_string($con,$user_mobile);
    $where .= " AND user='$safe_mobile'";
}

/* GAME FILTER */

if(!empty($game_name)){
    $safe_game = mysqli_real_escape_string($con,$game_name);
    $where .= " AND bazar='$safe_game'";
}

/* SESSION FILTER */

if(!empty($session)){
    $safe_session = mysqli_real_escape_string($con,$session);
    $where .= " AND game_type='$safe_session'";
}


/* ===============================
   FILTER TYPE LOGIC
================================ */

if($filter_type == 'win'){
    $where .= " AND is_status=1 AND is_loss=0";
}

/* Withdraw logic can be changed later
   depending on your withdraw rows */
// if($filter_type == 'withdraw'){
//     $where .= " AND bazar='withdraw'";
// }

/* Bid history handled in view */


/* ===============================
   EXECUTE QUERY
================================ */

$query  = "SELECT * FROM games $where ORDER BY sn DESC";
$select = mysqli_query($con,$query);


/* ===============================
   TABLE VIEW
================================ */

if(mysqli_num_rows($select) > 0){ ?>

<div class="table-responsive">
<style>

.table-custom{
    width:100%;
    border-collapse:separate;
    border-spacing:0 8px;
}

.table-custom thead th{
    background:#ffb100;
    color:#000;
    font-weight:700;
    text-align:center;
    padding:10px;
    font-size:14px;
}

.table-custom thead th:first-child{
    text-align:left;
    padding-left:15px;
}

.table-custom tbody tr{
    color:#fff;
    font-weight:500;
}

.row-green{
    background:#0a8f08;
}

.row-red{
    background:#ff4d4d;
}

.table-custom tbody td{
    padding:10px;
    text-align:center;
    border-right:1px solid rgba(255,255,255,0.3);
}

.table-custom tbody td:first-child{
    text-align:left;
    padding-left:15px;
    font-weight:600;
}

.table-custom tbody td:last-child{
    border-right:none;
}

</style>
<table class="table-custom">

<?php
/* ===============================
   TABLE HEADERS
================================ */

if($filter_type == 'withdraw'){ ?>

<thead class="table-header-orange">
<tr>
<th class="header-col desc">Description</th>
<th>Point</th>
<th>Balance</th>
</tr>
</thead>

<?php } elseif($filter_type == 'history'){ ?>

<thead class="table-header-orange">
<tr>
<th>Game</th>
<th>Number</th>
<th>Amount</th>
</tr>
</thead>

<?php } else { ?>

<thead class="table-header-orange">
<tr>
<th style="text-align:left;padding-left:15px;">Game (Type)</th>
<th>Bids</th>
<th>Win</th>
<th>PL</th>
</tr>
</thead>

<?php } ?>


<tbody>

<?php
while($row = mysqli_fetch_assoc($select)){

$bids = (float)$row['amount'];

$win = ($row['is_status'] == 1 && $row['is_loss'] == 0) ? ($bids * 9) : 0;

$pl = $bids - $win;


/* ===============================
   WITHDRAW VIEW
================================ */

if($filter_type == 'withdraw'){ ?>

<tr style="background:#ffffff;color:#000;">

<td>
Withdraw Request for <?php echo htmlspecialchars($row['bazar']); ?>
</td>

<td>
<?php echo $row['amount']; ?>
</td>

<td>
<?php echo $row['wallet_after'] ?? 0; ?>
</td>

</tr>

<?php }


/* ===============================
   BID HISTORY VIEW
================================ */

elseif($filter_type == 'history'){ ?>

<tr>

<td>
<?php echo htmlspecialchars($row['bazar']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['number']); ?>
</td>

<td>
<?php echo $row['amount']; ?>
</td>

</tr>

<?php }


/* ===============================
   DEFAULT VIEW
   (ALL / WIN / ADD)
================================ */

else{

$rowClass = ($pl >= 0) ? 'row-green' : 'row-red';

?>

<tr class="<?php echo $rowClass; ?>">

<td style="text-align:left;padding-left:15px;font-weight:bold;">
<?php echo htmlspecialchars($row['bazar']); ?>
(<?php echo htmlspecialchars($row['game_type']); ?>)
</td>

<td><?php echo $bids; ?></td>

<td><?php echo $win; ?></td>

<td><?php echo $pl; ?></td>

</tr>

<?php }

} ?>

</tbody>
</table>

</div>

<?php

} else {

?>

<div class="table-responsive">

<table class="table table-bordered">

<thead class="table-header-orange">
<tr>
<th>Game</th>
<th>Bids</th>
<th>Win</th>
<th>PL</th>
</tr>
</thead>

<tbody>

<tr>
<td colspan="4" style="background:white;color:black;padding:40px;text-align:center;">
<strong>No data found for the selected filters.</strong>
</td>
</tr>

</tbody>
</table>

</div>

<?php } ?>