<?php include('header.php'); ?>

<section class="content-header">
<div class="container-fluid">
<div class="row mb-2">

<div class="col-sm-6">
<h1>Jackpot Bid History</h1>
</div>

<div class="col-sm-6">
<ol class="breadcrumb float-sm-right">
<li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
<li class="breadcrumb-item active">Jackpot Bid History</li>
</ol>
</div>

</div>
</div>
</section>


<section class="content">

<div class="container-fluid">

<div class="card card-default">

<div class="card-header">
<h3 class="card-title">Filters</h3>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-4">

<label>Date</label>

<input type="date"
id="date"
value="<?php echo date('Y-m-d');?>"
class="form-control">

</div>


<div class="col-md-4">

<label>Market</label>

<select id="market" class="form-control">

<option value="">Select Market</option>

<?php

$q = mysqli_query($con,"SELECT * FROM jackpot_markets WHERE is_active='1'");

while($r=mysqli_fetch_assoc($q)){

?>

<option value="<?php echo $r['name']; ?>">
<?php echo $r['name']; ?>
</option>

<?php } ?>

</select>

</div>


<div class="col-md-4 mt-4">

<button id="fetchData" class="btn btn-primary mt-2">
Fetch Data
</button>

</div>

</div>

</div>

</div>

</div>

</section>



<section class="content">

<div class="container-fluid">

<div class="card">

<div class="card-header">
<h3 class="card-title">Jackpot Bid History</h3>
</div>

<div class="card-body">
<div class="table-responsive">
<table id="example1" class="table table-bordered table-striped table-sm">

<thead>

<tr>

<th>#</th>
<th>User</th>
<th>Mobile</th>
<th>Market</th>
<th>Jodi</th>
<th>Points</th>
<th>Status</th>
<th>Date</th>

</tr>

</thead>

<tbody id="tbody"></tbody>

</table>
</div>
</div>

</div>

</div>

</section>

<?php include('footer.php'); ?>

<script>

$('#fetchData').click(function(){

var date = $('#date').val();
var market = $('#market').val();

$.ajax({

type:"POST",
url:"jackpot-bid-history-ajax.php",
data:{date:date,market:market},

success:function(response){

$("#tbody").html(response);

}

});

});

</script>