<?php
include('header.php');

if (in_array(15, $HiddenProducts)) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // UI Mapping
    $gameMap = [
        'singleank'   => 'Single Ank',
        'jodi'        => 'Jodi',
        'singlepana'  => 'Single Pana',
        'doublepana'  => 'Double Pana',
        'triplepana'  => 'Triple Pana',
        'halfsangam'  => 'Half Sangam',
        'fullsangam'  => 'Full Sangam'
    ];

    // DB Column Mapping
    $columnMap = [
        'singleank'   => 'single',
        'jodi'        => 'jodi',
        'singlepana'  => 'singlepatti',
        'doublepana'  => 'doublepatti',
        'triplepana'  => 'triplepatti',
        'halfsangam'  => 'halfsangam',
        'fullsangam'  => 'fullsangam'
    ];

    // =========================
    // UPDATE LOGIC
    // =========================
    if (isset($_POST['submit'])) {

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo "<script>alert('Invalid CSRF');</script>";
        } else {

            $game = $_POST['game_play'];
            $input = floatval($_POST['price']); // 950

            if (array_key_exists($game, $columnMap)) {

                $column = $columnMap[$game];

                // Convert to DB format → 10/950
                $rate = "10/" . $input;

                mysqli_query($con, "UPDATE rates SET `$column`='$rate'");

                echo "<script>alert('Rate Updated Successfully'); window.location.href='starline-game-rate-new.php';</script>";
            } else {
                echo "<script>alert('Invalid Game');</script>";
            }
        }
    }

    // =========================
    // FETCH DATA (ONLY ONE ROW)
    // =========================
    $res = mysqli_query($con, "SELECT * FROM rates LIMIT 1");
    $ratesData = mysqli_fetch_assoc($res);
?>

<style>
.game-table th, .game-table td {
    text-align:center;
    vertical-align: middle;
}
</style>

<section class="content">
<div class="container-fluid">
<div class="row">
<div class="col-md-3"></div>

<div class="col-md-6">

<!-- FORM -->
<div class="card">
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

<div class="card-body">

<div class="form-group">
<label>Game Type</label>
<select name="game_play" class="form-control" required>
<option value="">Select Game</option>
<?php
foreach($gameMap as $key => $name){
    echo "<option value='$key'>$name</option>";
}
?>
</select>
</div>

<div class="form-group">
<label>Enter Win Amount (Example: 950 for 10/950)</label>
<input type="number" step="0.01" name="price" class="form-control" required>
</div>

<button type="submit" name="submit" class="btn btn-primary">Update</button>

</div>
</form>
</div>

<!-- TABLE -->
<div class="card">
<div class="card-body p-0">

<table class="table table-bordered game-table">
<thead style="background:#e67e22;color:white;">
<tr>
<th>Sn</th>
<th>Game</th>
<th>Rate</th>
<th>Win Amount</th>
</tr>
</thead>

<tbody>

<?php
$sn = 1;

foreach($gameMap as $key => $name){

    $dbColumn = $columnMap[$key];
    $dbRate = isset($ratesData[$dbColumn]) ? $ratesData[$dbColumn] : "10/0";

    // Split 10/950
    $parts = explode('/', $dbRate);
    $winAmount = isset($parts[1]) ? (float)$parts[1] : 0;
?>

<tr>
<td><?php echo $sn++; ?></td>
<td><?php echo $name; ?></td>
<td>10/<?php echo $winAmount; ?></td>
<td><?php echo $winAmount; ?></td>
</tr>

<?php } ?>

</tbody>
</table>

</div>
</div>

</div>
<div class="col-md-3"></div>
</div>
</div>
</section>

<?php
} else {
    echo "<script>window.location.href='unauthorized.php';</script>";
    exit();
}
include('footer.php');
?>