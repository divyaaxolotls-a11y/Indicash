<?php 
include('header.php'); 

$user_id = mysqli_real_escape_string($con, $_GET['userID']);

// Fetch User Info
$user_q = mysqli_query($con, "SELECT name, mobile FROM users WHERE mobile = '$user_id'");
$user_data = mysqli_fetch_assoc($user_q);

// Fetch Current Bank Info from existing bank_history table
$bank_q = mysqli_query($con, "SELECT * FROM bank_history WHERE user = '$user_id'");
$bank_data = mysqli_fetch_assoc($bank_q);
?>

<div class="content-wrapper">
    <section class="content pt-4">
        <div class="container-fluid">
            
            <div class="card shadow-sm border-0" style="border-radius: 15px; max-width: 800px; margin: 0 auto;">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary"><i class="fas fa-university mr-2"></i><b>User Bank Information</b></h5>
                    <a href="user-profile.php?userID=<?php echo $user_id; ?>" class="btn btn-sm btn-outline-dark border-0">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                
                <div class="card-body px-4">
                    <!-- User Section -->
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <label class="text-muted small mb-0">Full Name</label>
                            <h6 class="font-weight-bold"><?php echo $user_data['name'] ?? 'N/A'; ?></h6>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small mb-0">Registered Phone</label>
                            <h6 class="font-weight-bold"><?php echo $user_data['mobile'] ?? 'N/A'; ?></h6>
                        </div>
                    </div>

                    <hr>

                    <!-- Bank Details Section -->
                    <div class="bg-light p-4 rounded" style="border: 1px dashed #ced4da;">
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-0">Bank Name</label>
                                <h5 class="text-dark"><b><?php echo $bank_data['bank_name'] ?: 'Not Provided'; ?></b></h5>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="text-muted small mb-0">Account Holder Name</label>
                                <h5 class="text-uppercase"><b><?php echo $bank_data['holder'] ?: 'Not Provided'; ?></b></h5>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-0">Bank Account Number</label>
                                <h5 class="text-dark"><b><?php echo $bank_data['ac'] ?: 'Not Provided'; ?></b></h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-0">IFSC Code</label>
                                <h5 class="text-dark"><b><?php echo $bank_data['ifsc'] ?: 'Not Provided'; ?></b></h5>
                            </div>

                            <div class="col-md-6">
                                <label class="text-muted small mb-0">Payment Mode</label>
                                <div><span class="badge badge-success px-3"><?php echo $bank_data['mode'] ?: 'Bank Transfer'; ?></span></div>
                            </div>

                            <div class="col-md-6 text-right">
                                <label class="text-muted small mb-0">Last Updated</label>
                                <p class="small text-muted mb-0"><?php echo $bank_data['updated_at'] ?: 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button class="btn btn-primary px-4" onclick="window.print()">
                            <i class="fas fa-print mr-2"></i> Print Details
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include('footer.php'); ?>