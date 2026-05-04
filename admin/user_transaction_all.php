<?php include('header.php'); 

$user_id = $_GET['user_id'];
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
            <h1>All Transactions</h1>
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
                                              
                                                <?php
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
                                                    }
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
                                                    }
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
                                              
                                               <?php
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
                                                    }
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