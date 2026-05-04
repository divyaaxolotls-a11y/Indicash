<?php 

include('header.php');
if (in_array(13, $HiddenProducts)){

// if($idd!='admin@gmail.com'){
    // $result = mysqli_query($con, "SELECT wr.*, u.name FROM auto_deposits wr INNER JOIN users u ON wr.user = u.mobile WHERE  u.refcode = '$refcodeq' ORDER BY wr.sn DESC");
// }
// else{
   $result = mysqli_query($con,"select * from auto_deposits order by sn desc");
// }
?>
<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Deposite Points Request</h1>

          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Deposite Request</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                    <button class="btn btn-primary">Depository Points</button>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Mobile Number</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Pay id</th>
                    <th>Created at</th>
                    <th>Status</th>
                    <!--<th>View</th>-->
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                    <?php
                        while ($row = mysqli_fetch_array($result)) { 
                            $user_id = $row['sn'];
                            $user_mobile = $row['mobile'];
                            $user = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$user_mobile' ");
                            $fetch = mysqli_fetch_array($user);
                            $withdraw_details = mysqli_fetch_array(mysqli_query($con,"select * from auto_deposits where sn='$user_id' ORDER BY sn DESC"));
                    ?>
                        <tr>
                            <td><?php $i++; echo $i; ?></td>
                            <td><?php echo htmlspecialchars($fetch['name']); ?> <a href="user-profile.php?userID=<?php echo htmlspecialchars($user_id); ?>"><i class="mdi mdi-link"></i></a></td>
                            <td><?php echo htmlspecialchars($row['mobile']); ?></td>               
                            <td><?php echo htmlspecialchars($row['amount']); ?></td>
                            <td><?php echo htmlspecialchars($row['method']); ?></td>
                            <td><?php echo htmlspecialchars($row['pay_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                            <td>
                               <?php
                            if ($row['status'] == 1) {
                                echo "<span class='badge badge-success'>APPROVED</span>";
                            } elseif ($row['status'] == 0) {
                                echo "<span class='badge badge-warning'>Pending</span>";
                            } elseif ($row['status'] == 2) {
                                echo "<span class='badge badge-danger'>Rejected</span>";
                            } else {
                           echo "<span class='badge badge-secondary'>Unknown Status</span>"; // Handle any unexpected status values
                            }
                            ?>
                            </td>
                            <!--<td class="text-center"><a href="#ViewRequest<?php echo $user_id; ?>" data-toggle="modal" style="font-size:20px;"><i class="fas fa-eye"></i></a></td>-->
                            <td class="text-center">
                                <?php if($row['status'] == 0){ ?>
                                <a href="#RequestApproved<?php echo htmlspecialchars($row['sn']); ?>" data-toggle="modal" class="btn btn-success mb-4">Approved</a>
                                <a href="#RequestRejected<?php echo htmlspecialchars($row['sn']); ?>" data-toggle="modal" class="btn btn-danger">Rejected</a>
                                <?php } else { echo "Action taken"; } ?>
                            </td>
                        </tr>   
                        <!--View Withdraw Request-->
                        <div class="modal fade" id="ViewRequest<?php echo $user_id; ?>">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <!-- Modal Header -->
                                    <div class="modal-header">
                                        <h4 class="modal-title">Deposite Point Details</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <!-- Modal body -->
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-sm-3 border"><b>User Name</b></div>
                                            <div class="col-sm-3 border">
                                                <?php echo $fetch['name']; ?> <a href="user-profile.php?userID=<?php echo $user_id; ?>"><i class="mdi mdi-link"></i></a>
                                            </div>
                                             <div class="col-sm-3 border"><b>Request Points</b></div>
                                            <div class="col-sm-3 border"><?php echo htmlspecialchars($row['amount']); ?></div>
                                            <div class="col-sm-3 border"><b>Request Number</b></div>
                                            <div class="col-sm-3 border"><b>Payment Method</b></div>
                                            <div class="col-sm-3 border"><span class="badge badge-success"><?php echo htmlspecialchars($row['mode']); ?></span></div>
                                            <div class="col-sm-3 border"><b>Paytm</b></div>
                                            <div class="col-sm-3 border"><span ><?php echo htmlspecialchars($row['paytm']); ?></span></div>
                                            <div class="col-sm-3 border"><b>GPay</b></div>
                                            <div class="col-sm-3 border"><span ><?php echo htmlspecialchars($row['gpay']); ?></span></div>
                                            <div class="col-sm-3 border"><b>Phonpe</b></div>
                                            <div class="col-sm-3 border"><span ><?php echo htmlspecialchars($row['phonepe']); ?></span></div>
                                            <div class="col-sm-3 border"><b>A/C no</b></div>
                                            <div class="col-sm-3 border"><span ><?php echo htmlspecialchars($row['ac']); ?></span></div>
                                            <div class="col-sm-3 border"><b>A/C Holder Name</b></div>
                                            <div class="col-sm-3 border"><span ><?php echo htmlspecialchars($row['holder']); ?></span></div>
                                            <div class="col-sm-3 border"><b>IFSC</b></div>
                                            <div class="col-sm-3 border"><span ><?php echo htmlspecialchars($row['ifsc']); ?></span></div>
                                            <div class="col-sm-3 border"><b>Request Date</b></div>
                                            <div class="col-sm-3 border"><?php echo htmlspecialchars($row['date']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Request Rejected-->
                        <div class="modal fade" id="RequestRejected<?php echo $row['sn']; ?>">
                            <div class="modal-dialog">
                              <div class="modal-content">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                  <h4 class="modal-title">Request Rejected</h4>
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <!-- Modal body -->
                                <div class="modal-body">
                                    <form method="post" autocomplete="off" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['sn']); ?>" required/>
                                        <input type="hidden" name="txn_id" value="<?php echo htmlspecialchars($row['sn']); ?>" required/>
                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>" required/>
                                        <div class="form-group">
                                            <!-- Modal footer -->
                                            <div class="modal-footer">
                                                <button class="btn btn-success" type="submit" name="requestRejected">Rejected</button>
                                              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                              </div>
                            </div>
                        </div>
                       <!--Request Approved-->
    <div class="modal fade" id="RequestApproved<?php echo $row['sn']; ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Request Approved</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <form method="post" autocomplete="off" action="auto_deposite_display.php">
                    <input type="text" name="id" value="<?php echo htmlspecialchars($row['sn']); ?>" required />
                    <input type="text" name="mobile" value="<?php echo htmlspecialchars($row['mobile']); ?>">
                    <input type="text" name="amount" value="<?php echo htmlspecialchars($row['amount']); ?>" required />
                    <div class="form-group">
                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button class="btn btn-success" type="submit" name="requestApproved">Approve</button>

                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

                    <?php
                        }
                    ?>
                  </tbody>
                </table>
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

if (isset($_POST['requestRejected'])) {
    $id = $_POST['id'];
    $remark = $_POST['remark'];
    $createDate = date("Y-m-d H:i:s");
    $txnID = $_POST['txn_id'];
    $userID = $_POST['user_id'];

    // Check if ID is numeric to avoid invalid values
    if (!is_numeric($id)) {
        die("Invalid ID.");
    }

    // Prepare and execute update query using a prepared statement
    $stmt = $con->prepare("UPDATE auto_deposits SET status = ? WHERE sn = ?");
    $status = 2;
    $stmt->bind_param("ii", $status, $id);
    $stmt->execute();

    $remark = 'Deposite request rejected ' . $id;
    log_action($remark);  // Call the function to log the action
    // Fetch mobile and amount from the database using a prepared statement
    $stmt = $con->prepare("SELECT mobile, amount FROM auto_deposits WHERE sn = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $info = $result->fetch_assoc();

    if ($info) {
        $mobile = htmlspecialchars($info['mobile']);
        $amount = htmlspecialchars($info['amount']);
    }

    // Redirect after processing
    echo "<script>window.location.href= 'auto_deposite_display.php';</script>";
}

if (isset($_POST['requestApproved'])) {
    $id = $_POST['id'];
    $pointsAdd = $_POST['amount'];
    $createDate = date("Y-m-d H:i:s");

    // Validate pointsAdd to be a positive number
    if (!is_numeric($pointsAdd) || $pointsAdd <= 0) {
        die("Invalid points amount.");
    }

    // Update the request status to approved using prepared statement
    $stmt = $con->prepare("UPDATE auto_deposits SET status = ? WHERE sn = ?");
    $status = 1;
    $stmt->bind_param("ii", $status, $id);
    $stmt->execute();

    // Fetch user's mobile based on deposit ID
    $stmt = $con->prepare("SELECT mobile FROM auto_deposits WHERE sn = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mobileRow = $result->fetch_assoc();
    $userMobile = htmlspecialchars($mobileRow['mobile']);

    // Update user's wallet with the points
    $stmt = $con->prepare("UPDATE users SET wallet = wallet + ? WHERE mobile = ?");
    $stmt->bind_param("is", $pointsAdd, $userMobile);
    $stmt->execute();

    // Prepare transaction record
    $stamp = time();
    $stmt = $con->prepare("INSERT INTO transactions (user, amount, type, remark, created_at, owner) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $remark = 'Manual Deposit Request Approved';
    $type = 1;
    $stmt->bind_param("siisii", $userMobile, $pointsAdd, $type, $remark, $stamp, $userMobile);
    $stmt->execute();

    // Fetch referral code for user
    $stmt = $con->prepare("SELECT refcode FROM users WHERE mobile = (SELECT mobile FROM auto_deposits WHERE sn = ?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $refcode = $result->fetch_assoc()['refcode'];

    // Fetch referrer's user ID using the referral code
    $stmt = $con->prepare("SELECT sn FROM users WHERE ref_id = ?");
    $stmt->bind_param("s", $refcode);
    $stmt->execute();
    $result = $stmt->get_result();
    $referrer_id = $result->fetch_assoc()['sn'];

    // Fetch referral bonus from settings
    $stmt = $con->prepare("SELECT data FROM settings WHERE data_key = 'ref_bonus'");
    $stmt->execute();
    $result = $stmt->get_result();
    $ref_bonus = $result->fetch_assoc()['data'];

    // Update referrer's wallet with referral bonus
    $stmt = $con->prepare("UPDATE users SET wallet = wallet + ? WHERE sn = ?");
    $stmt->bind_param("ii", $ref_bonus, $referrer_id);
    $stmt->execute();

    // Fetch referrer's mobile number
    $stmt = $con->prepare("SELECT mobile FROM users WHERE sn = ?");
    $stmt->bind_param("i", $referrer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $refMobile = htmlspecialchars($result->fetch_assoc()['mobile']);

    // Insert referral bonus transaction record
    $stmt = $con->prepare("INSERT INTO transactions (user, amount, type, remark, created_at, owner) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $remark = 'Deposit Referral Bonus Added';
    $stmt->bind_param("siisii", $refMobile, $ref_bonus, $type, $remark, $stamp, $refMobile);
    $stmt->execute();
    
    $remark = 'Deposite request approved ' . $id;
    log_action($remark);  // Call the function to log the action

    // Redirect after processing
    echo "<script>window.location.href= 'auto_deposite_display.php';</script>";
}

?>

<?php 
}else{ 
echo "<script>
window.location.href = 'unauthorized.php';
</script>";
exit();
}
include('footer.php'); ?>