<?php include('header.php'); ?>

<section class="content-header">
<div class="container-fluid">
<div class="row mb-2">
<div class="col-sm-6">
<h1>Jackpot Markets</h1>
</div>

<div class="col-sm-6">
<ol class="breadcrumb float-sm-right">
<li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
<li class="breadcrumb-item active">Jackpot Markets</li>
</ol>
</div>
</div>
</div>
</section>

<section class="content">
<div class="container-fluid">
<div class="row">
<div class="col-12">

<div class="card">

<div class="card-header">
<h3 class="card-title">
<a href="#AddNewMarket" data-toggle="modal" class="btn btn-primary">Add Market</a>
</h3>
</div>

<div class="card-body">

<table id="example1" class="table table-bordered table-striped">
<thead>
<tr>
<th>#</th>
<th>Market Name</th>
<th>Close Time</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php

$markets = mysqli_query($con,"SELECT * FROM jackpot_markets ORDER BY name ASC");

$i=1;

while($row=mysqli_fetch_assoc($markets))
{

?>

<tr>

<td><?php echo $i; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['close']; ?></td>

<td class="text-center">

<?php if($row['is_active']==0){ ?>

<a href="?Active=<?php echo $row['sn']; ?>" class="btn btn-sm btn-danger">Inactive</a>

<?php }else{ ?>

<a href="?Deactive=<?php echo $row['sn']; ?>" class="btn btn-sm btn-success">Active</a>

<?php } ?>

</td>

<td>

<a href="?Delete=<?php echo $row['sn']; ?>" class="btn btn-sm btn-danger">Delete</a>

</td>

</tr>

<?php

$i++;

}

?>

</tbody>

</table>

</div>
</div>
</div>
</div>
</div>
</section>

<!-- Add Market Modal -->

<div class="modal fade" id="AddNewMarket">

<div class="modal-dialog">

<div class="modal-content bg-primary">

<div class="modal-header">
<h4 class="modal-title">Add New Jackpot Market</h4>
<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>

<form method="POST">

<div class="modal-body">

<div class="form-group">
<label>Market Name</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="form-group">
<label>Close Time</label>
<input type="time" name="close" class="form-control" required>
</div>

</div>

<div class="modal-footer justify-content-between">

<button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>

<button type="submit" name="AddMarket" class="btn btn-outline-light">Save</button>

</div>

</form>

</div>
</div>
</div>

<?php


// INSERT MARKET

if(isset($_POST['AddMarket']))
{

$name=$_POST['name'];
$close=$_POST['close'];

$insert=mysqli_query($con,"INSERT INTO jackpot_markets(name,close,is_active) VALUES('$name','$close','1')");

if($insert)
{
echo "<script>window.location.href='jackpot-market-list.php'</script>";
}

}


// DELETE MARKET

if(isset($_GET['Delete']))
{

$id=$_GET['Delete'];

mysqli_query($con,"DELETE FROM jackpot_markets WHERE sn='$id'");

echo "<script>window.location.href='jackpot-market-list.php'</script>";

}


// ACTIVATE MARKET

if(isset($_GET['Active']))
{

$id=$_GET['Active'];

mysqli_query($con,"UPDATE jackpot_markets SET is_active='1' WHERE sn='$id'");

echo "<script>window.location.href='jackpot-market-list.php'</script>";

}


// DEACTIVATE MARKET

if(isset($_GET['Deactive']))
{

$id=$_GET['Deactive'];

mysqli_query($con,"UPDATE jackpot_markets SET is_active='0' WHERE sn='$id'");

echo "<script>window.location.href='jackpot-market-list.php'</script>";

}

include('footer.php');

?>