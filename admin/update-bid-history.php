<?php include('header.php'); 

$id = $_GET['id'];

$select = mysqli_query($con, "SELECT * FROM `games` WHERE `sn`='$id' ");
$row = mysqli_fetch_array($select);


if(isset($_POST['update'])){
    $openDigit = $_POST['openDigit'];
    $amount = $_POST['amount'];

$remark = 'Bid Updated  To Number ' . $openDigit . ' Amount ' . $amount;
log_action($remark);  // Call the function to log the action
            
            
    $update = mysqli_query($con, "UPDATE `games` SET `number`='$openDigit',`amount`='$amount' WHERE `sn`='$id'");

    if($update){
        echo "<script>window.location.href='bid-history.php';</script>";
    }
}

?>



<!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center">
                        <h4 class="page-title">Update Bid History</h4>
                        <div class="ml-auto text-right">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Update Bid History</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            
            <section class="content">
                <div class="container-fluid">
                
                <!--Filters-->
                <div class="row">
                    <div class="card w-100">
                        <div class="card-body">
                            <h5 class="card-title m-b-0">Update</h5>
                            <hr>
                            <form method="post">
                             
                                <div class="form-group">
                                    <label>Bet</label>
                                    <input type="number" name="openDigit" class="form-control" value="<?php echo $row['number']; ?>"/>
                                </div>
                                
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="number" name="amount" class="form-control" value="<?php echo $row['amount']; ?>"/>
                                </div>
                                
                               
                                
                                <div class="form-group">
                                  <button type="submit" name="update" class="btn btn-primary" >Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                
                
            </div>
            </section>
            
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->


      

  

<?php

include('footer.php'); ?>
<script>
    $('#pana').select2({
      placeholder: 'Select Pana',
      allowClear: true
    });

</script>
