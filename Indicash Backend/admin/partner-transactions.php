<?php include('header.php'); 

$user_id = $main_partner_mail;
?>

<style>
    .user-img{
        margin-left: auto;
        margin-right: auto;
    }
    .personalInfo > tbody > tr > th {
        font-weight:bold;
    }
    
    .paymentInfo > tbody > tr > th {
        font-weight:bold;
    }
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>All Transactions  <a href="partner-transactions.php?all=true"> <button class='btn btn-danger'>View All</button><a/> </h1> 
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">All Transactions</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            
            
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Update Partner Wallet</h3>
                
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form method="post">
                <div class="card-body">
                  <button class="btn btn-info"> Wallet balance - 
                <?php
                $get_wallet = mysqli_fetch_array(mysqli_query($con,"select wallet from admin where email='$main_partner_mail'"));
                echo $get_wallet['wallet'];
                 ?>
                </button>
                  <div class="form-group">
                    <label>Amount</label>
                    <input type="number" name="min_deposit" value="<?php echo $valuesRow['min_deposit']; ?>" class="form-control" required />
                  </div>
                  
                  <div class="form-group">
                    <label>Transaction type</label>
                    <select name="telegram" class="form-control">
                        <option value="" selected disabled>Select type</option>
                        <option value="1">Deposit</option>
                        <option value="0">Withdraw</option>
                    </select>
                  </div>
                 
                </div>
                <!-- /.card-body -->
                
                <?php if(isset($_REQUEST['AddValues'])){
  						$amount = $_REQUEST['min_deposit'];
 						 $type = $_REQUEST['telegram'];
  if($type == "0"){
    $walletAdd = mysqli_query($con,"update admin set wallet=wallet-$amount where email='$main_partner_mail'");

    mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`, `game_id`, `batch_id`) VALUES ('$main_partner_mail','$amount','0','Deducted by admin','admin','$stamp','0','0')");

  } else {
     $walletAdd = mysqli_query($con,"update admin set wallet=wallet+$amount where email='$main_partner_mail'");

    mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`, `game_id`, `batch_id`) VALUES ('$main_partner_mail','$amount','1','Added by admin','admin','$stamp','0','0')");

  }
  if($walletAdd){
     echo "<script>window.location= 'partner-transactions.php';</script>";
  }
                  } ?>
  
  

                <div class="card-footer">
                  <button type="submit" name="AddValues" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div>
            <!-- /.card -->
            
            
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                    <!-- <button class="btn btn-primary">Un-approved Users List</button> -->
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
             
                
                <!--Wallet Transation History-->
                <div class="row">
                    <div class="card w-100">
                            <div class="card-body">
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#walletAll" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">All</span></a> </li>
                                    <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#walletCredit" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Credit</span></a> </li>
                                    <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#walletDebit" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Debit</span></a> </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content tabcontent-border">
                                    <div class="tab-pane active" id="walletAll" role="tabpanel">
                                        <table id="walletAllTable" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>S. No.</th>
                                                    <th>Points</th>
                                                    <th>Transaction Note</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $allWallet = mysqli_query($con, "SELECT * FROM `transactions` WHERE `user`='$user_id' ORDER BY sn DESC");
                                                    $i = 1;
                                                    while($row = mysqli_fetch_array($allWallet)){
                                                ?>
                                                <tr>
                                                    <td><?php echo $i; ?></td>
                                                    <td>
                                                        <?php 
                                                            if($row['type'] == '1'){
                                                                echo "<span class='badge badge-success'> +".$row['amount']."</span>";
                                                            }else{
                                                                echo "<span class='badge badge-danger'>-".$row['amount']."</span>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $row['remark']; ?></td>
                                                    <td><?php echo date('h:i A d-m-Y',$row['created_at']); ?></td>
                                                </tr>
                                                <?php
                                                    $i++;
                                                    }
                                                ?>
                                              
                                                <?php  if($_REQUEST['all']){
                                                    $allWallet = mysqli_query($con, "SELECT * FROM `transactions_archive` WHERE `user`='$user_id' ORDER BY sn DESC");
                                                    $i = 1;
                                                    while($row = mysqli_fetch_array($allWallet)){
                                                ?>
                                                <tr>
                                                    <td><?php echo $i; ?></td>
                                                    <td>
                                                        <?php 
                                                            if($row['type'] == '1'){
                                                                echo "<span class='badge badge-success'> +".$row['amount']."</span>";
                                                            }else{
                                                                echo "<span class='badge badge-danger'>-".$row['amount']."</span>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $row['remark']; ?></td>
                                                    <td><?php echo date('h:i A d-m-Y',$row['created_at']); ?></td>
                                                </tr>
                                                <?php
                                                    $i++;
                                                    } }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane  p-20" id="walletCredit" role="tabpanel">
                                        <table id="walletCreditTable" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>S. No.</th>
                                                    <th>Points</th>
                                                    <th>Transaction Note</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $allWallet = mysqli_query($con, "SELECT * FROM `transactions` WHERE `user`='$user_id' AND `type`='1' ORDER BY sn DESC");
                                                    $i = 1;
                                                    while($row = mysqli_fetch_array($allWallet)){
                                                ?>
                                                <tr>
                                                    <td><?php echo $i; ?></td>
                                                    <td>
                                                        <?php 
                                                            if($row['type'] == '1'){
                                                                echo "<span class='badge badge-success'> +".$row['amount']."</span>";
                                                            }else{
                                                                echo "<span class='badge badge-danger'>-".$row['amount']."</span>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $row['remark']; ?></td>
                                                    <td><?php echo date('h:i A d-m-Y',$row['created_at']); ?></td>
                                                </tr>
                                                <?php
                                                    $i++;
                                                    }
                                                ?>
                                              
                                               <?php
                                              if($_REQUEST['all']){
                                                    $allWallet = mysqli_query($con, "SELECT * FROM `transactions_archive` WHERE `user`='$user_id' AND `type`='1' ORDER BY sn DESC");
                                                    $i = 1;
                                                    while($row = mysqli_fetch_array($allWallet)){
                                                ?>
                                                <tr>
                                                    <td><?php echo $i; ?></td>
                                                    <td>
                                                        <?php 
                                                            if($row['type'] == '1'){
                                                                echo "<span class='badge badge-success'> +".$row['amount']."</span>";
                                                            }else{
                                                                echo "<span class='badge badge-danger'>-".$row['amount']."</span>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $row['remark']; ?></td>
                                                    <td><?php echo date('h:i A d-m-Y',$row['created_at']); ?></td>
                                                </tr>
                                                <?php
                                                    $i++;
                                                    } }
                                                ?>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane p-20" id="walletDebit" role="tabpanel">
                                        <table id="walletDebitTable" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>S. No.</th>
                                                    <th>Points</th>
                                                    <th>Transaction Note</th>
                                                    <th>Date</th>
                                                    <th>TXN ID</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $allWallet = mysqli_query($con, "SELECT * FROM `transactions` WHERE `user`='$user_id' AND `type`='0' ORDER BY sn DESC");
                                                    $i = 1;
                                                    while($row = mysqli_fetch_array($allWallet)){
                                                ?>
                                                <tr>
                                                    <td><?php echo $i; ?></td>
                                                    <td>
                                                        <?php 
                                                            if($row['type'] == '1'){
                                                                echo "<span class='badge badge-success'> +".$row['amount']."</span>";
                                                            }else{
                                                                echo "<span class='badge badge-danger'>-".$row['amount']."</span>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $row['remark']; ?></td>
                                                    <td><?php echo date('h:i A d-m-Y',$row['created_at']); ?></td>
                                                </tr>
                                                <?php
                                                    $i++;
                                                    }
                                                ?>
                                              
                                               <?php if($_REQUEST['all']){
                                                    $allWallet = mysqli_query($con, "SELECT * FROM `transactions_archive` WHERE `user`='$user_id' AND `type`='0' ORDER BY sn DESC");
                                                    $i = 1;
                                                    while($row = mysqli_fetch_array($allWallet)){
                                                ?>
                                                <tr>
                                                    <td><?php echo $i; ?></td>
                                                    <td>
                                                        <?php 
                                                            if($row['type'] == '1'){
                                                                echo "<span class='badge badge-success'> +".$row['amount']."</span>";
                                                            }else{
                                                                echo "<span class='badge badge-danger'>-".$row['amount']."</span>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $row['remark']; ?></td>
                                                    <td><?php echo date('h:i A d-m-Y',$row['created_at']); ?></td>
                                                </tr>
                                                <?php
                                                    $i++;
                                                    } }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->


<?php
include('footer.php'); 
?>