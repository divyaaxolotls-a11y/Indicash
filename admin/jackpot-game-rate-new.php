<?php
include('header.php');

if (in_array(15, $HiddenProducts)) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // =========================
    // UPDATE LOGIC (ONLY JODI)
    // =========================
    if (isset($_POST['submit'])) {

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo "<script>alert('Invalid CSRF');</script>";
        } else {

            $input = floatval($_POST['price']); // e.g. 950

            // Convert to DB format → 10/950
            $rate = "10/" . $input;

            mysqli_query($con, "UPDATE rates SET `jodi`='$rate'");

            echo "<script>alert('Jackpot Rate Updated'); window.location.href='jackpot-game-rate-new.php';</script>";
        }
    }

    // =========================
    // FETCH DATA
    // =========================
    $res = mysqli_query($con, "SELECT jodi FROM rates LIMIT 1");
    $row = mysqli_fetch_assoc($res);

    $dbRate = isset($row['jodi']) ? $row['jodi'] : "10/0";

    // Split 10/950
    $parts = explode('/', $dbRate);
    $winAmount = isset($parts[1]) ? (float)$parts[1] : 0;
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
<label>Jackpot Game (Jodi)</label>
<input type="text" class="form-control" value="Jodi" readonly>
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

<tr>
<td>1</td>
<td>Jodi (Jackpot)</td>
<td><?php echo $dbRate; ?></td>
<td><?php echo $winAmount; ?></td>
</tr>

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