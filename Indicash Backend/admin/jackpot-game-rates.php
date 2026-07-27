<?php 
include('header.php');

$select = mysqli_query($con, "SELECT * FROM `jackpot_game_rates` ");
$row = mysqli_fetch_array($select);

if(isset($_POST['submit'])){

    $jackpot1 = $_POST['jackpot1'];
    $jackpot2 = $_POST['jackpot2'];

    $update = mysqli_query($con, "UPDATE `jackpot_game_rates` SET 
        `jackpot_value1`='$jackpot1',
        `jackpot_value2`='$jackpot2'
        WHERE `id`='1'
    ");

    if($update){
        echo "<script>window.location.href='jackpot-game-rates.php';</script>";
    }
}
?>

<section class="content-header">
<div class="container-fluid">
<div class="row mb-2">
<div class="col-sm-6">
<h1>Jackpot Game Rates</h1>
</div>

<div class="col-sm-6">
<ol class="breadcrumb float-sm-right">
<li class="breadcrumb-item"><a href="Dashboard">Home</a></li>
<li class="breadcrumb-item active">Jackpot Game Rates</li>
</ol>
</div>

</div>
</div>
</section>


<section class="content">

<div class="container-fluid">
<div class="row">

<div class="col-md-2"></div>

<div class="col-md-8">

<div class="card card-success">

<div class="card-header">
<h3 class="card-title">Jackpot Rates</h3>
</div>

<form method="POST">

<div class="card-body">

<h4>Jackpot Number (00-99)</h4>

<div class="row">

<div class="col-sm-6">
<label class="mb-2 mr-sm-2">Value 1:</label>

<input type="number"
class="form-control"
min="0"
value="<?php echo $row['jackpot_value1']; ?>"
name="jackpot1"
required />

</div>


<div class="col-sm-6">

<label class="mb-2 mr-sm-2">Value 2:</label>

<input type="number"
class="form-control"
min="0"
value="<?php echo $row['jackpot_value2']; ?>"
name="jackpot2"
required />

</div>

</div>

</div>


<div class="card-footer">
<button type="submit" name="submit" class="btn btn-primary">Submit</button>
</div>

</form>

</div>

</div>

<div class="col-md-2"></div>

</div>
</div>

</section>

<?php include('footer.php'); ?>