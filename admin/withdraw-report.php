<?php include('header.php'); 
if (in_array(12, $HiddenProducts)){
?>
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Withdraw Report</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Withdraw Report</li>
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
                <button class="btn btn-primary">Withdraw Report</button>
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
                <th>Points</th>
                <th>Date</th>
                <th>Screenshot</th>
                <th>Status</th>
                <th>View</th>
              </tr>
              </thead>
              <tbody>
                <?php 
                    // Proper validation for user input and database result handling
                    $result = mysqli_query($con, "SELECT wr.*, u.name FROM withdraw_requests wr INNER JOIN users u ON wr.user = u.mobile WHERE wr.status = '1' ORDER BY wr.sn DESC");
                    $i = 0;
                    while ($row = mysqli_fetch_array($result)) {
                        // Sanitize output with htmlspecialchars()
                        $userName = htmlspecialchars($row['name']);
                        $userMobile = htmlspecialchars($row['user']);
                        $requestAmount = htmlspecialchars($row['amount']);
                        $createdAt = htmlspecialchars($row['created_at']);
                        $screenshot = htmlspecialchars($row['screenshot_with']);
                        $status = $row['status'];
                ?>
                    <tr>
                        <td><?php $i++; echo $i; ?></td>
                        <td><?php echo $userName; ?> <a href="user-profile.php?userID=<?php echo htmlspecialchars($row['user']); ?>"><i class="mdi mdi-link"></i></a></td>
                        <td><?php echo $userMobile; ?></td>
                        <td><?php echo $requestAmount; ?></td>
                        <td><?php echo $createdAt; ?></td>
                        <td><a target="_blank" href="<?php echo $screenshot; ?>"><img src="<?php echo $screenshot; ?>" style="width:100px;"/></a></td>
                        <td>
                            <?php
                                // Use proper status check and output
                                if ($status == 1) {
                                    echo "<span class='badge badge-success'>Accepted</span>";  
                                } elseif ($status == 0) {
                                    echo "<span class='badge badge-warning'>Pending</span>"; 
                                } else {
                                    echo "<span class='badge badge-danger'>Rejected</span>";    
                                }
                            ?>
                        </td>
                        <td class="text-center"><a href="#ViewRequest<?php echo $i; ?>" data-toggle="modal" style="font-size:20px;"><i class="fas fa-eye"></i></a></td>
                    </tr>

                    <!-- Modal View -->
                    <div class="modal fade" id="ViewRequest<?php echo $i; ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Withdraw Request Details</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-3 border"><b>User Name</b></div>
                                        <div class="col-sm-3 border"><?php echo $userName; ?> <a href="user-profile.php?userID=<?php echo htmlspecialchars($row['user']); ?>"><i class="mdi mdi-link"></i></a></div>
                                        <div class="col-sm-3 border"><b>Request Points</b></div>
                                        <div class="col-sm-3 border"><?php echo $requestAmount; ?></div>
                                        <div class="col-sm-3 border"><b>Request Number</b></div>
                                        <div class="col-sm-3 border">#<?php echo htmlspecialchars($row['sn']); ?></div>
                                        <div class="col-sm-3 border"><b>Payment Method</b></div>
                                        <div class="col-sm-3 border"><span class="badge badge-success"><?php echo htmlspecialchars($row['mode']); ?></span></div>
                                        <div class="col-sm-3 border"><b>Paytm</b></div>
                                        <div class="col-sm-3 border"><span><?php echo htmlspecialchars($row['paytm']); ?></span></div>
                                        <div class="col-sm-3 border"><b>Phonpe</b></div>
                                        <div class="col-sm-3 border"><span><?php echo htmlspecialchars($row['phonepe']); ?></span></div>
                                        <div class="col-sm-3 border"><b>A/C no</b></div>
                                        <div class="col-sm-3 border"><span><?php echo htmlspecialchars($row['ac']); ?></span></div>
                                        <div class="col-sm-3 border"><b>A/C Holder Name</b></div>
                                        <div class="col-sm-3 border"><span><?php echo htmlspecialchars($row['holder']); ?></span></div>
                                        <div class="col-sm-3 border"><b>IFSC</b></div>
                                        <div class="col-sm-3 border"><span><?php echo htmlspecialchars($row['ifsc']); ?></span></div>
                                        <div class="col-sm-3 border"><b>Request Date</b></div>
                                        <div class="col-sm-3 border"><?php echo date('h:i A d-m-Y', strtotime($row['created_at'])); ?></div>
                                    </div>
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
}else{ 
echo "<script>
window.location.href = 'unauthorized.php';
</script>";
exit();
}
include('footer.php'); ?>
