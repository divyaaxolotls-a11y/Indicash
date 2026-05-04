<?php include('header.php'); 
// Handle Profile Field Updates
if (isset($_POST['updateField'])) {
    $fieldName = $_POST['field_name']; // name, email, or mobile
    $newValue = htmlspecialchars($_POST['field_value'], ENT_QUOTES, 'UTF-8');
    $oldMobile = $_POST['old_mobile'];

    // Update the database
    $stmt = $con->prepare("UPDATE users SET $fieldName = ? WHERE mobile = ?");
    $stmt->bind_param("ss", $newValue, $oldMobile);
    
    if ($stmt->execute()) {
        // If we updated the mobile number, we must change the URL to reflect the new ID
        $redirectID = ($fieldName == 'mobile') ? $newValue : $oldMobile;
        echo "<script>window.location.href='user-profile.php?userID=$redirectID&msg=Updated';</script>";
    } else {
        echo "<script>alert('Error updating field');</script>";
    }
    $stmt->close();
}
$user_id = $_GET['userID'];
$select = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$user_id' ");
$row = mysqli_fetch_array($select);


$selects = mysqli_query($con, "SELECT * FROM `withdraw_details` WHERE `user`='$user_id' ");
$withdraw_details = mysqli_fetch_array($selects);
?>

<style>
    .user-img {
        margin-left: auto;
        margin-right: auto;
    }
    .personalInfo > tbody > tr > th {
        font-weight:bold;
    }
    
    .paymentInfo > tbody > tr > th {
        font-weight:bold;
    }
    .personalInfo input.form-control {
    border: none;
    border-bottom: 1px dashed #ccc;
    background: transparent;
    padding-left: 0;
    border-radius: 0;
}
.personalInfo input.form-control:focus {
    box-shadow: none;
    border-bottom: 1px solid #007bff;
}
.personalInfo .btn-outline-primary:hover {
    background-color: transparent;
    color: #0056b3;
}
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>User Profile</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard1.php">Home</a></li>
              <li class="breadcrumb-item active">User Profile</li>
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
                        <!-- /.card-header -->
              <div class="card-body">
              <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                              
                              <?php if(isset($_REQUEST['error'])){ ?>
                              <div class="alert alert-danger" role="alert">
                               <?php echo $_REQUEST['error']; ?>
                              </div>
                              <?php } ?>
                              
                                <h4 class="card-title m-b-0 text-center">USER DETAILS</h4>
                                <hr>
                                <!--<div class="row bg-dark">-->
                                <!--    <img src="dist/img/user3-128x128.jpg" alt="user" width="80" class="rounded-circle user-img">-->
                                <!--</div>-->
                            </div>
                            <div class="comment-widgets scrollable">
                                <!-- Comment Row -->
                                
                                <div class="d-flex flex-row">
                                    <table class="table">
                                        <tr>
                                            <th>User Status</th>
                                            <td class="text-center">
                                                <?php
                                                    if($row['active'] == 0){
                                                ?>
                                                    <a href="user-profile.php?UserActive=<?php echo $row['mobile']; ?>"><span class="badge badge-danger">No</span></a>
                                                <?php
                                                    }else{
                                                ?>
                                                    <a href="user-profile.php?UserDeactive=<?php echo $row['mobile']; ?>"><span class="badge badge-success">Yes</span></a>
                                                <?php
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr >
                                            <th>Betting Status</th>
                                            <td class="text-center">
                                                <?php
                                                    if($row['verify'] == 0){
                                                ?>
                                                <a href="user-profile.php?BettingActive=<?php echo $row['mobile']; ?>"><span class="badge badge-danger">No</span></a>
                                                <?php
                                                    }else{
                                                ?>
                                                <a href="user-profile.php?BettingDeactive=<?php echo $row['mobile']; ?>"><span class="badge badge-success">Yes</span></a>
                                                <?php
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                        
                                    </table>
                                </div>
                                <div class=" text-center">
                                        <h2 style="color:#999">Wallet Balance</h2>
                                        <h3>
                                            <?php
                                                
                                                echo $row['wallet']
                                            ?>
                                            <b style="color:green; font-size:15px;">Points</b>
                                        </h3>
                                        
                                        <div class="comment-footer">
                                            <a href="#addPoint" data-toggle="modal" class="btn btn-success btn-md">Add Points</a>
                                            <a href="#withdrowPoints" data-toggle="modal" class="btn btn-danger btn-md">Withdraw Points</a>
                                        </div>
                                </div>
                                
                            </div>
                        </div>
                        <?php
                            // Active Betting Status
                                                if(isset($_GET['BettingActive'])){
                                                    $id = $_GET['BettingActive'];
                                                    $registrationDate = date("Y-m-d H:i:s");
                                                    
                                                    $updateUser = mysqli_query($con, "UPDATE `users` SET `verify`='1' WHERE `mobile`='$id' AND `verify`='0'");
                                                    if($updateUser){
                                                        echo "<script>window.location.href='user-manage.php';</script>";
                                                    }
                                                    
                                                }
                                                
                                                // Deactive Betting Status
                                                if(isset($_GET['BettingDeactive'])){
                                                    $id = $_GET['BettingDeactive'];
                                                    $registrationDate = date("Y-m-d H:i:s");
                                                    
                                                    $updateUser = mysqli_query($con, "UPDATE `users` SET `verify`='0' WHERE `mobile`='$id' AND `verify`='1'");
                                                    if($updateUser){
                                                        echo "<script>window.location.href='user-manage.php';</script>";
                                                    }
                                                    
                                                }
                                                
                                                
                                                // Active User Status
                                                if(isset($_GET['UserActive'])){
                                                    $id = $_GET['UserActive'];
                                                    $registrationDate = date("Y-m-d H:i:s");
                                                    
                                                    $updateUser = mysqli_query($con, "UPDATE `users` SET `active`='1'  WHERE `mobile`='$id' AND `active`='0'");
                                                    if($updateUser){
                                                        echo "<script>window.location.href='user-manage.php';</script>";
                                                    }
                                                    
                                                }
                                                
                                                // Deactive User Status
                                                if(isset($_GET['UserDeactive'])){
                                                    $id = $_GET['UserDeactive'];
                                                    $registrationDate = date("Y-m-d H:i:s");
                                                    
                                                    $updateUser = mysqli_query($con, "UPDATE `users` SET `active`='0' WHERE `mobile`='$id' AND `active`='1'");
                                                    if($updateUser){
                                                        echo "<script>window.location.href='user-manage.php';</script>";
                                                    }
                                                    
                                                }
                        ?>
                        
                    </div>
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title m-b-0">Personal Information</h5>
                            </div>
                            <table class="table personalInfo">
                                <tbody>
                                    <tr>
                                        <th>Full Name</th>
                                        <td>
                                            <?php //echo $row['name']; ?>
                                            <form method="POST" class="d-flex align-items-center">
                                                <input type="text" name="field_value" value="<?php echo $row['name']; ?>" class="form-control form-control-sm mr-2">
                                                <input type="hidden" name="field_name" value="name">
                                                <input type="hidden" name="old_mobile" value="<?php echo $row['mobile']; ?>">
                                                <button type="submit" name="updateField" class="btn btn-sm btn-outline-primary border-0">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>
                                            <?php //echo $row['email']; ?>
                                             <form method="POST" class="d-flex align-items-center">
                                                <input type="email" name="field_value" value="<?php echo $row['email']; ?>" class="form-control form-control-sm mr-2">
                                                <input type="hidden" name="field_name" value="email">
                                                <input type="hidden" name="old_mobile" value="<?php echo $row['mobile']; ?>">
                                                <button type="submit" name="updateField" class="btn btn-sm btn-outline-primary border-0">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </form>
                                         </td>
                                    </tr>
                                    <tr>
                                        <th>Mobile Number</th>
                                        <td>
                                            <?php //echo $row['mobile']; ?>
                                            <form method="POST" class="d-flex align-items-center">
                                                <input type="text" name="field_value" value="<?php echo $row['mobile']; ?>" class="form-control form-control-sm mr-2">
                                                <input type="hidden" name="field_name" value="mobile">
                                                <input type="hidden" name="old_mobile" value="<?php echo $row['mobile']; ?>">
                                                <button type="submit" name="updateField" class="btn btn-sm btn-outline-primary border-0" onclick="return confirm('Warning: Changing the mobile number will change the unique login ID for this user. Continue?')">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <!--<tr>-->
                                    <!--    <th>Password</th>-->
                                    <!--    <td><?php echo $row['password']; ?></td>-->
                                    <!--</tr>-->
                                    <tr>
                                        <th>Registration Date</th>
                                        <td><?php echo date('d-m-Y',$row['created_at']); ?></td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                            <div class="text-center mb-4 mt-4">
                                <div class="comment-footer pt-3">
                                    <a href="https://api.whatsapp.com/send?phone=91<?php echo $row['mobile']; ?>" target="_blank" class="btn btn-success btn-md">WhatsApp</a>
                                    <a href="tel:+91 <?php echo $row['mobile']; ?>" target="_blank" class="btn btn-danger btn-md">Call</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <!--Payment Information-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title m-b-0">Payment Information</h5>
                            </div>
                            <table class="table paymentInfo">
                                <?php
                                    $bank_details = mysqli_query($con, "SELECT * FROM `bank_history` WHERE `user`='$user_id'");
                                    $bank = mysqli_fetch_array($bank_details);
                                    
                                ?>
                                <tbody>
                                    <tr>
                                        <th>Account Holder Name</th>
                                        <td><?php echo $bank['holder']; ?></td>
                                       
                                    </tr>
                                    <tr>
                                         <th>Account Number</th>
                                        <td><?php echo $bank['ac']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>IFSC Code</th>
                                        <td><?php echo $bank['ifsc']; ?></td>
                                        
                                      
                                    </tr>
                                    <!--<tr>-->
                                    <!--    <th>UPI</th>-->
                                    <!--    <td><?php echo $bank['upi']; ?></td>-->
                                    <!--</tr>-->
                                    <!--   <tr>-->
                                    <!--    <th>PhonePe</th>-->
                                    <!--    <td><?php echo $bank['phonepe']; ?></td>-->
                                    <!--</tr>-->
                                    <!--   <tr>-->
                                    <!--    <th>GPay</th>-->
                                    <!--    <td><?php echo $bank['gpay']; ?></td>-->
                                    <!--</tr>-->
                                    <!--   <tr>-->
                                    <!--    <th>PayTM</th>-->
                                    <!--    <td><?php echo $bank['paytm']; ?></td>-->
                                    <!--</tr>-->
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                

                <!--Withdwaw Points Request-->
                <div class='row'>
                    <div class="card w-100">
                        <div class="card-body">
                            <h5 class="card-title m-b-0">Withdwaw Points Requests</h5>
                            <hr>
                            <div class="table-responsive">
                                    <table id="withdraw_table" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>S. No.</th>
                                                <th>Points</th>
                                                <th>Request No.</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>View</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                                <?php
                             
                             
                                                                         
                                            
                                            $num_results_on_page0 = 10;  
                                            if (isset($_GET["page0"])) {
                                            	$page0  = $_GET["page0"]; 
                                            	} 
                                            	else{ 
                                            	$page0=1;
                                            	};  
                                            $start_from0 = ($page0-1) * $num_results_on_page0;  
                                            
                                            $search_url_add0 = "&userID=".$user_id;
                                            
                                            
                                                $result0 = mysqli_query($con,"select * from withdraw_requests  WHERE user='$user_id' order by sn desc LIMIT $start_from0, $num_results_on_page0");
                                                
                                                
                                                $result_db0 = mysqli_query($con,"SELECT COUNT(sn) FROM withdraw_requests  WHERE user='$user_id'"); 
                                            
                                            $action_url0 = "&page=".$page.$search_url_add0;
                                            
                                            
                                            $row_db0 = mysqli_fetch_row($result_db0);  
                                            $total_pages0 = $row_db0[0];  
                                            
                                            $page0 = isset($_GET['page0']) && is_numeric($_GET['page0']) ? $_GET['page0'] : 1;
                                            
                                            
                                                                         
                                                                         
                                                                        
                                                                           
                                                                     $i0 = 1;
                                                 $i0 = (($page0-1)*10)+1;
                                                                         while($row1 = mysqli_fetch_array($result0)){
                                                                         ?>
                                                                         
                                                
                                           
                                            <tr>
                                                <td><?php echo $i0; ?></td>
                                                
                                                <td><?php echo $row1['amount']; ?></td>
                                                <td><?php echo $row1['sn']; ?></td>
                                                <td><?php echo $row1['created_at']; ?></td>
                                                <td>
                                                    <?php
                                                        if($row1['status'] == 1){
                                                          echo "<span class='badge badge-success'>Accepted</span>";  
                                                        }elseif($row1['status'] == 0){
                                                            echo "<span class='badge badge-warning'>Pending</span>"; 
                                                        }else{
                                                            echo "<span class='badge badge-danger'>Rejected</span>";    
                                                        }
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="#ViewRequest<?php echo $i0; ?>" data-toggle="modal" style="font-size:20px;"><i class="fas fa-eye"></i></a>
                                                </td>
                                                <td class="text-center">
                                                    <?php if($row1['status'] == 0){ ?>
                                                    <a href="#RequestApproved<?php echo $i0; ?>" data-toggle="modal" class="btn btn-success">Approved</a>
                                                    <a href="#RequestRejected<?php echo $i0; ?>" data-toggle="modal" class="btn btn-danger">Rejected</a>
                                                    <?php } else { echo "No Action"; } ?>
                                                </td>
                                            </tr>
                                            
                                            
                                            <!--View Withdraw Request-->
                                            <div class="modal fade" id="ViewRequest<?php echo $i0; ?>">
                                                <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                
                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                    <h4 class="modal-title">Withdraw Request Details</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    
                                                    <!-- Modal body -->
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-sm-3 border"><b>User Name</b></div>
                                                            <div class="col-sm-3 border">
                                                                <?php echo $row['name']; ?> </a>
                                                            </div>
                                                            <div class="col-sm-3 border"><b>Request Points</b></div>
                                                            <div class="col-sm-3 border"><?php echo $row1['amount']; ?></div>
                                                            <div class="col-sm-3 border"><b>Request Number</b></div>
                                                            <div class="col-sm-3 border">#<?php echo $row1['sn']; ?></div>
                                                            <div class="col-sm-3 border"><b>Payment Method</b></div>
                                                            <div class="col-sm-3 border"><span class="badge badge-success"><?php echo $row1['mode']; ?></span></div>
                                                            <div class="col-sm-3 border"><b>Request Date</b></div>
                                                            <div class="col-sm-3 border"><?php echo $row1['created_at']; ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                          
                                            <!--Request Rejected-->
                                            <div class="modal fade" id="RequestRejected<?php echo $i0; ?>">
                                                <div class="modal-dialog">
                                                  <div class="modal-content">
                                                  
                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                      <h4 class="modal-title">Request Approved</h4>
                                                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    
                                                    <!-- Modal body -->
                                                    <div class="modal-body">
                                                        <form method="post" autocomplete="off" enctype="multipart/form-data">
                                                            <input type="hidden" name="id" value="<?php echo $row1['sn']; ?>" required/>
                                                            <input type="hidden" name="txn_id" value="<?php echo $row1['sn']; ?>" required/>
                                                          
                                                            
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
                                            <div class="modal fade" id="RequestApproved<?php echo $i0; ?>">
                                                <div class="modal-dialog">
                                                  <div class="modal-content">
                                                  
                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                      <h4 class="modal-title">Request Approved</h4>
                                                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    
                                                    <!-- Modal body -->
                                                    <div class="modal-body">
                                                        <form method="post" autocomplete="off" enctype="multipart/form-data">
                                                            <input type="hidden" name="id" value="<?php echo $row1['sn']; ?>" required/>
                                                          
                                                            
                                                            <div class="form-group">
                                                                <!-- Modal footer -->
                                                                <div class="modal-footer">
                                                                    <button class="btn btn-success" type="submit" name="requestApproved">Approved</button>
                                                                  <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                        
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            <?php
                                                $i0++;
                                                }
                                            ?>
                                            
                                        </tbody>
                                    </table>
                                    
                                    
                                       <?php if (ceil($total_pages0 / $num_results_on_page0) > 0): 
                                    
                                     ?>
                <ul class="pagination">
                  <?php if ($page0 > 1): ?>
                  <li class="prev page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page0-1 ?><?php echo $search_url_add0; ?>">Prev</a></li>
                  <?php endif; ?>

                  <?php if ($page0 > 3): ?>
                  <li class="start page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=1<?php echo $search_url_add0; ?>">1</a></li>
                  <li class="dots page-item">...</li>
                  <?php endif; ?>

                  <?php if ($page0-2 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page0=<?php echo $page0-2 ?><?php echo $search_url_add0; ?>"><?php echo $page-2 ?></a></li><?php endif; ?>
                  <?php if ($page0-1 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page0=<?php echo $page0-1 ?><?php echo $search_url_add0; ?>"><?php echo $page0-1 ?></a></li><?php endif; ?>

                  <li class="currentpage page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page0=<?php echo $page0 ?><?php echo $search_url_add0; ?>"><?php echo $page0 ?></a></li>

                  <?php if ($page0+1 < ceil($total_pages0 / $num_results_on_page0)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page0=<?php echo $page0+1 ?><?php echo $search_url_add0; ?>"><?php echo $page0+1 ?></a></li><?php endif; ?>
                  <?php if ($page0+2 < ceil($total_pages0 / $num_results_on_page0)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page0=<?php echo $page0+2 ?><?php echo $search_url_add0; ?>"><?php echo $page0+2 ?></a></li><?php endif; ?>

                  <?php if ($page0 < ceil($total_pages0 / $num_results_on_page0)-2): ?>
                  <li class="dots page-item">...</li>
                  <li class="end page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page0=<?php echo ceil($total_pages0 / $num_results_on_page0) ?><?php echo $search_url_add0; ?>"><?php echo ceil($total_pages0 / $num_results_on_page0) ?></a></li>
                  <?php endif; ?>

                  <?php if ($page0 < ceil($total_pages0 / $num_results_on_page0)): ?>
                  <li class="next page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page0=<?php echo $page0+1 ?><?php echo $search_url_add0; ?>">Next</a></li>
                  <?php endif; ?>
                </ul>
                <?php endif; ?>
                                    
                                    
                            </div>
                        </div>
                    </div>
                </div>
                
                
                
             <div class='row'>
               <div class="card w-100">
                  <div class="card-body">
                     <h5 class="card-title m-b-0">Bid History</h5>
                     <hr>
                     <div class="table-responsive">
                        <table id="withdraw_table" class="table table-striped table-bordered">
                           <thead>
                              <tr>
                                 <th>S. No.</th>
                                 <th>Game Name</th>
                                 <th>Game Type</th>
                                 <th>Digits</th>
                                 <th>Points</th>
                                 <th>Date</th>
                                 <th>Placed on</th>
                              </tr>
                           </thead>
                           <tbody>
                             
                             <?php
                             
                             
                             

$num_results_on_page = 10;  
if (isset($_GET["page"])) {
	$page  = $_GET["page"]; 
	} 
	else{ 
	$page=1;
	};  
$start_from = ($page-1) * $num_results_on_page;  

$search_url_add = "&userID=".$user_id;


    $result = mysqli_query($con,"select * from games  WHERE user='$user_id' order by sn desc LIMIT $start_from, $num_results_on_page");
    
    
    $result_db = mysqli_query($con,"SELECT COUNT(sn) FROM games  WHERE user='$user_id'"); 

$action_url = "&page=".$page.$search_url_add;


$row_db = mysqli_fetch_row($result_db);  
$total_pages = $row_db[0];  

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;


                             
                             
                            
                               
                         $i = 1;
     $i = (($page-1)*10)+1;
                             while($row = mysqli_fetch_array($result)){
                             ?>
                              <tr>
                                 <td><?php echo $i; ?></td>
                                <td><?php echo htmlspecialchars($row['bazar']); ?></td>
                                 <td><?php echo htmlspecialchars($row['game_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['number']); ?></td>
                                <td><?php echo htmlspecialchars($row['amount']); ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo date("h:i A d/m/Y",$row['created_at']); ?></td>
                              </tr>
                             <?php $i++; } ?>
                           </tbody>
                        </table>
                          <?php if (ceil($total_pages / $num_results_on_page) > 0): 
                                    
                                     ?>
                <ul class="pagination">
                  <?php if ($page > 1): ?>
                  <li class="prev page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page-1 ?><?php echo $search_url_add; ?>">Prev</a></li>
                  <?php endif; ?>

                  <?php if ($page > 3): ?>
                  <li class="start page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=1<?php echo $search_url_add; ?>">1</a></li>
                  <li class="dots page-item">...</li>
                  <?php endif; ?>

                  <?php if ($page-2 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page-2 ?><?php echo $search_url_add; ?>"><?php echo $page-2 ?></a></li><?php endif; ?>
                  <?php if ($page-1 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page-1 ?><?php echo $search_url_add; ?>"><?php echo $page-1 ?></a></li><?php endif; ?>

                  <li class="currentpage page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page ?><?php echo $search_url_add; ?>"><?php echo $page ?></a></li>

                  <?php if ($page+1 < ceil($total_pages / $num_results_on_page)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page+1 ?><?php echo $search_url_add; ?>"><?php echo $page+1 ?></a></li><?php endif; ?>
                  <?php if ($page+2 < ceil($total_pages / $num_results_on_page)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page+2 ?><?php echo $search_url_add; ?>"><?php echo $page+2 ?></a></li><?php endif; ?>

                  <?php if ($page < ceil($total_pages / $num_results_on_page)-2): ?>
                  <li class="dots page-item">...</li>
                  <li class="end page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo ceil($total_pages / $num_results_on_page) ?><?php echo $search_url_add; ?>"><?php echo ceil($total_pages / $num_results_on_page) ?></a></li>
                  <?php endif; ?>

                  <?php if ($page < ceil($total_pages / $num_results_on_page)): ?>
                  <li class="next page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page+1 ?><?php echo $search_url_add; ?>">Next</a></li>
                  <?php endif; ?>
                </ul>
                <?php endif; ?>
                
                     </div>
                  </div>
               </div>
            </div>
                
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
                             
                             
                                                                         
                                            
                                            $num_results_on_page2 = 10;  
                                            if (isset($_GET["page2"])) {
                                            	$page2  = $_GET["page2"]; 
                                            	} 
                                            	else{ 
                                            	$page2=1;
                                            	};  
                                            $start_from2 = ($page2-1) * $num_results_on_page2;  
                                            
                                            $search_url_add2 = "&userID=".$user_id;
                                            
                                            
                                                $result2 = mysqli_query($con,"select * from transactions  WHERE user='$user_id' order by sn desc LIMIT $start_from2, $num_results_on_page2");
                                                
                                                
                                                $result_db2 = mysqli_query($con,"SELECT COUNT(sn) FROM transactions  WHERE user='$user_id'"); 
                                            
                                            $action_url2 = "&page=".$page.$search_url_add2;
                                            
                                            
                                            $row_db2 = mysqli_fetch_row($result_db2);  
                                            $total_pages2 = $row_db2[0];  
                                            
                                            $page2 = isset($_GET['page2']) && is_numeric($_GET['page2']) ? $_GET['page2'] : 1;
                                            
                                            
                                                                         
                                                                         
                                                                        
                                                                           
                                                                     $i2 = 1;
                                                 $i2 = (($page2-1)*10)+1;
                                                                         while($row = mysqli_fetch_array($result2)){
                                                                         ?>
                                                                         
                                                
                                                <tr>
                                                    <td><?php echo $i2; ?></td>
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
                                                    $i2++;
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                        
                                          <?php if (ceil($total_pages2 / $num_results_on_page2) > 0): 
                                    
                                     ?>
                <ul class="pagination">
                  <?php if ($page2 > 1): ?>
                  <li class="prev page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page2=<?php echo $page2-1 ?><?php echo $search_url_add2; ?>">Prev</a></li>
                  <?php endif; ?>

                  <?php if ($page2 > 3): ?>
                  <li class="start page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page2=1<?php echo $search_url_add2; ?>">1</a></li>
                  <li class="dots page-item">...</li>
                  <?php endif; ?>

                  <?php if ($page2-2 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page2=<?php echo $page2-2 ?><?php echo $search_url_add2; ?>"><?php echo $page2-2 ?></a></li><?php endif; ?>
                  <?php if ($page2-1 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page2=<?php echo $page2-1 ?><?php echo $search_url_add2; ?>"><?php echo $page2-1 ?></a></li><?php endif; ?>

                  <li class="currentpage page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page2 ?><?php echo $search_url_add2; ?>"><?php echo $page2 ?></a></li>

                  <?php if ($page2+1 < ceil($total_pages2 / $num_results_on_page2)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page2=<?php echo $page2+1 ?><?php echo $search_url_add2; ?>"><?php echo $page2+1 ?></a></li><?php endif; ?>
                  <?php if ($page2+2 < ceil($total_pages2 / $num_results_on_page2)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page2=<?php echo $page2+2 ?><?php echo $search_url_add2; ?>"><?php echo $page2+2 ?></a></li><?php endif; ?>

                  <?php if ($page2 < ceil($total_pages2 / $num_results_on_page2)-2): ?>
                  <li class="dots page-item">...</li>
                  <li class="end page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page2=<?php echo ceil($total_pages2 / $num_results_on_page2) ?><?php echo $search_url_add2; ?>"><?php echo ceil($total_pages2 / $num_results_on_page2) ?></a></li>
                  <?php endif; ?>

                  <?php if ($page2 < ceil($total_pages2 / $num_results_on_page2)): ?>
                  <li class="next page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page2=<?php echo $page2+1 ?><?php echo $search_url_add2; ?>">Next</a></li>
                  <?php endif; ?>
                </ul>
                <?php endif; ?>
                
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
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            
                              <a href="user_transaction_all.php?user_id=<?php echo $user_id; ?>"><button class="btn btn-danger">View All</button></a><span>This may take a while to load</span>
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

<!--Withdrawal Points-->
<div class="modal fade" id="withdrowPoints">
        <div class="modal-dialog">
          <div class="modal-content">
          
            <!-- Modal Header -->
            <div class="modal-header">
              <h4 class="modal-title">Withdraw Points</h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <!-- Modal body -->
            <div class="modal-body">
                <form method="post" autocomplete="off">
                                                                <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label>Withdraw Points</label>
                        <input type="number" name="pointsWithdwaw" value="" class="form-control" placeholder="Enter Points Here" required/>
                    </div>
                    
                    <div class="form-group">
                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button class="btn btn-success" type="submit" name="WithdwawPoints">Submit</button>
                          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
                <?php
                    if(isset($_POST['WithdwawPoints'])){
                        
                        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                            // Alert the user and redirect back to transaction.php
                            echo "<script>
                                alert('Invalid CSRF token. Please refresh the page and try again.');
                            </script>";
                            exit; // Stop further execution
                        }

                        $pointsAdd = $_POST['pointsWithdwaw'];
                        $createDate = date("Y-m-d H:i:s");
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $TXN = 'TXN'.$dateString.$fourRandomDigit;
                        
                        $stamp = time();
                        
                        $walletAdd = mysqli_query($con,"update users set wallet=wallet-'$pointsAdd' where mobile='$user_id'");
                        mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`owner`) VALUES ('$user_id','$pointsAdd','0','Points Withdraw By Admin','$stamp','admin@gmail.com')");
         $created_atss = date('Y-m-d h:i:sa');

         
         $query = "INSERT INTO `withdraw_requests`(`user`, `amount`, `mode`, `status`, `ac`, `ifsc`, `holder`,`date`) 
          VALUES ('$user_id', '$pointsAdd', 'Points Withdraw By Admin', '1', '0', '0', '0','$created_atss')";

// Execute the query
$result = mysqli_query($con, $query);
                        
                        // $walletAdd = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                        //                                     VALUES ('$user_id','$pointsAdd','-','$TXN', 'Points Withdraw By Admin', '$createDate')");
            			sendNotification("Admin withdraw $pointsAdd from your wallet","Money withdraw from wallet",$user_id);
                      
                            if($walletAdd){
                                echo "<script>window.location.href= '';</script>";
                            }
                    }
                ?>
            </div>
          </div>
        </div>
      </div>

<!--Add Points-->
    <div class="modal fade" id="addPoint">
        <div class="modal-dialog">
          <div class="modal-content">
          
            <!-- Modal Header -->
            <div class="modal-header">
              <h4 class="modal-title">Add Points</h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <!-- Modal body -->
            <div class="modal-body">
                <form method="post" autocomplete="off">
                                            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label>Add Points</label>
                        <input type="number" name="pointsAdd" value="" class="form-control" placeholder="Enter Points Here" required />
                    </div>
                    
                    <div class="form-group">
                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button class="btn btn-success" type="submit" name="AddPoints">Submit</button>
                          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
                <?php
                    if(isset($_POST['AddPoints'])){
                        
                      if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_POST['csrf_token']) {
    // Alert the user and redirect back to transaction.php
    echo "<script>
        alert('Invalid CSRF token. Please refresh the page and try again.');
    </script>";
    exit; // Stop further execution
}

                      
                        $pointsAdd = $_POST['pointsAdd'];
                        $createDate = date("Y-m-d H:i:s");
                        $dateString = date('Ymd');
                        $fourRandomDigit = rand(1000,9999);
                        $TXN = 'TXN'.$dateString.$fourRandomDigit;
                        
                      if($_SESSION['userID'] != $main_admin_mail){
                       
                        if(mysqli_num_rows(mysqli_query($con,"select sn from admin where wallet >= $pointsAdd AND email='".$_SESSION['userID']."'")) == 0){
                          
                       //   echo "<script>alert('select sn from admin where wallet >= $pointsAdd' AND email='".$_SESSION['userID']."');</script>";
                          
                          echo "<script>window.location.href= 'user-profile.php?userID=".$user_id."&error=You%20dont%20have%20enough%20wallet%20balance';</script>";
                          
                          return;
                          
                        } else {
                       
                          $walletAdd = mysqli_query($con,"update admin set wallet=wallet-$pointsAdd where email='".$_SESSION['userID']."'");
                          
                         $remark = 'Points added By Admin '.$user_id;
                        log_action($remark);
                          
                          mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`, `game_id`, `batch_id`) VALUES ('".$_SESSION['userID']."','$pointsAdd','0','Deposit to user $user_id','".$_SESSION['userID']."','$stamp','0','0')");
                        }
                        
                      }
                        
                        $stamp = time();
                        
                       $walletAdd = mysqli_query($con,"update users set wallet=wallet+'$pointsAdd' where mobile='$user_id'");
                        mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`,`owner`) VALUES ('$user_id','$pointsAdd','1','Points Added By Admin','$stamp','admin@gmail.com')");
                        
                        
                        
                            
    $created_at = date("Y-m-d H:i:s");
$updated_at = date("Y-m-d H:i:s");
$created_ats = date('Y-m-d h:i:sa'); // optional, but consistent format is better

$sql = "INSERT INTO auto_deposits (mobile, amount, pay_id, method, status, created_at, date, updated_at) 
        VALUES ('$user_id', $pointsAdd, 0, 'Added By Admin', 1, '$created_at', '$created_ats', '$updated_at')";

if ($con->query($sql) === TRUE) {
}
              
                        sendNotification("Admin added $pointsAdd to you wallet","Money added in wallet",$user_id);
         
                        
                        // $walletAdd = mysqli_query($con, "INSERT INTO `wallet`(`user_id`, `balance`, `operator`, `transactionID`, `description`, `created_at`) 
                        //                                     VALUES ('$user_id','$pointsAdd','+','$TXN', 'Points Added By Admin', '$createDate')");
            
                            if($walletAdd){
                                echo "<script>window.location.href= 'user-profile.php?userID=".$user_id."';</script>";
                            }
                    }
                ?>
            </div>
          </div>
        </div>
      </div>
      

  
<!--Change M-Pin-->
  <div class="modal fade" id="ChangeMpin">
    <div class="modal-dialog">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Change M-Pin</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label>M-Pin</label>
                    <input type="text" name="mPin" value="<?php echo $row['mPin']; ?>" pattern="[0-9]{4}" class="form-control" placeholder="Enter M-Pin Here" required/>
                </div>
                
                <div class="form-group">
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button class="btn btn-success" type="submit" name="ChangeMpin">Submit</button>
                      <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>

<?php 
    if(isset($_POST['requestRejected'])){
        $id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
    $remark = htmlspecialchars($_POST['remark'], ENT_QUOTES, 'UTF-8');
    $createDate = date("Y-m-d H:i:s");
    $txn_id = htmlspecialchars($_POST['txn_id'], ENT_QUOTES, 'UTF-8');

        
        mysqli_query($con,"update withdraw_requests set status='2' where sn='$id'");
        
        $info = mysqli_fetch_array(mysqli_query($con,"select user, amount from withdraw_requests where sn='$id'"));
        $mobile = $info['user'];
        $amount = $info['amount'];
        
        mysqli_query($con,"UPDATE users set wallet=wallet+$amount where mobile='$mobile'");
    
        $withdrawUpdate = mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`, `game_id`, `batch_id`) VALUES ('$mobile','$amount','1','Withdraw cancelled by our team','user','$stamp','0','0')");
  
         $remark = 'Withdraw cancelled '.$mobile.'amount'.$amount;
        log_action($remark);
        //$withdrawUpdate = mysqli_query($con, "UPDATE `withdraw_point_request` SET `remark`='$remark',`accept_date`='$createDate',`payment_receipt`='',`status`='2' WHERE `id`='$id'");
                                                    
        if($withdrawUpdate){
            //$walletRowDelete = mysqli_query($con, "DELETE FROM `wallet` WHERE `user_id`='$user_id' AND `transactionID`='$txn_id'");
             echo "<script>window.location.href= '';</script>";
            // if($walletRowDelete){
            //     echo "<script>window.location.href= '';</script>";
            // }
        }
    }



    // Approved request
    if(isset($_POST['requestApproved'])){
        $id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
    $remark = htmlspecialchars($_POST['remark'], ENT_QUOTES, 'UTF-8');
    $createDate = date("Y-m-d H:i:s");
                                                   
        
        // $target_dir = "images/";
        // $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        
        // $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            
        // }
        
        
          $withdrawUpdate=  mysqli_query($con,"update withdraw_requests set status='1' where sn='$id'");
      
         $remark = 'Withdraw request approved '.$id;
        log_action($remark);                                                          
       // $withdrawUpdate = mysqli_query($con, "UPDATE `withdraw_point_request` SET `remark`='$remark',`accept_date`='$createDate',`payment_receipt`='$target_file',`status`='1' WHERE `id`='$id'");
                                                    
        if($withdrawUpdate){
            echo "<script>window.location.href= '';</script>";
        }
    }


    if(isset($_POST['ChangeMpin'])){
    $mPin = htmlspecialchars($_POST['mPin'], ENT_QUOTES, 'UTF-8');
    $updateDate = date("Y-m-d H:i:s");
        
        $changePinM = mysqli_query($con, "UPDATE `users` SET `mPin`='$mPin', `updated_at`='$updateDate' WHERE `user_id`='$user_id'");
        
                 $remark = 'Mpin changed '.$id;
        log_action($remark);
        if($changePinM){
            echo "<script>window.location.href= '';</script>";
        }
    }


include('footer.php'); 
?>