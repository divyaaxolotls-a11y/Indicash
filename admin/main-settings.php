<?php include('header.php'); 
if (in_array(14, $HiddenProducts)){

  $selectValues = mysqli_query($con, "SELECT * FROM `settings` ");
  while($row= mysqli_fetch_array($selectValues)){
      $valuesRow[$row['data_key']] = $row['data'];
  }
  $valuesRowCount = mysqli_num_rows($selectValues);
?>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Main Settings</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Main Settings</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

<!-- Main content -->
<section class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Add Value's Limit</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form method="post">
                <div class="card-body">
                    


                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

  <div class="form-group">
                    <label>Days to Keep Bet History</label>
                    <input type="number" name="history_delete_days" value="<?php echo htmlspecialchars($valuesRow['history_delete_days']); ?>" class="form-control" min="2" max="10" step="1" required />
                  </div>
                  
                  
                  <div class="form-group">
                    <label>Signup Reward</label>
                    <input type="number" name="signup_reward" value="<?php echo htmlspecialchars($valuesRow['signup_reward']); ?>" class="form-control" required />
                  </div>
                        <div class="form-group">
                    <label>Minimum Balance After Withdraw</label>
                    <input type="number" name="minBalanceAfterWithdraw" value="<?php echo htmlspecialchars($valuesRow['minBalanceAfterWithdraw']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label>Minimum Deposit</label>
                    <input type="number" name="min_deposit" value="<?php echo htmlspecialchars($valuesRow['min_deposit']); ?>" class="form-control" required />
                  </div>
                  
                       <div class="form-group">
                    <label>Maximum Withdraw</label>
                    <input type="number" name="max_deposit" value="<?php echo htmlspecialchars($valuesRow['max_deposit']); ?>" class="form-control" required />
                  </div>
                   <div class="form-group">
                    <label>Deposite Refferal Bonus</label>
                    <input type="number" name="ref_bonus" value="<?php echo htmlspecialchars($valuesRow['ref_bonus']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label>Minimum Withdraw</label>
                    <input type="number" name="min_withdraw" value="<?php echo htmlspecialchars($valuesRow['min_withdraw']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group" style="display:none;">
                    <label>Paytm UPI Address</label>
                    <input type="text" name="upi" value="<?php echo htmlspecialchars($valuesRow['upi']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group" style="display:none;">
                    <label>Paytm UPI MCC</label>
                    <input type="number" name="merchant" value="<?php echo htmlspecialchars($valuesRow['merchant']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group" style="display:none;">
                    <label>PhonePe UPI Address</label>
                    <input type="text" name="upi_2" value="<?php echo htmlspecialchars($valuesRow['upi_2']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group" style="display:none;">
                    <label>PhonePe UPI MCC</label>
                    <input type="number" name="merchant_2" value="<?php echo htmlspecialchars($valuesRow['merchant_2']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group" style="display:none;">
                    <label>GPAY UPI Address</label>
                    <input type="text" name="upi_3" value="<?php echo htmlspecialchars($valuesRow['upi_3']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group"style="display:none;">
                    <label>GPAY UPI MCC</label>
                    <input type="number" name="merchant_3" value="<?php echo htmlspecialchars($valuesRow['merchant_3']); ?>" class="form-control" required />
                  </div>
                  
                   <div class="form-group">
                    <label>telegram Enable/Disable</label>
                    <select name="telegram" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['telegram'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['telegram'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
                  
                    <div class="form-group">
                    <label>Telegram details</label>
                    <input type="text" name="telegram_details" value="<?php echo htmlspecialchars($valuesRow['telegram_details']); ?>" class="form-control" required />
                  </div>
                  
                  
                  <div class="form-group">
                    <label>Whatsapp Support</label>
                    <select name="whatsapp_active" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['whatsapp_active'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['whatsapp_active'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Whatsapp</label>
                    <input type="number" name="whatsapp" value="<?php echo htmlspecialchars($valuesRow['whatsapp']); ?>" class="form-control" required />
                  </div>
                  
                         <div class="form-group">
                    <label>Whatsapp Support Mo.</label>
                    <input type="number" name="whatsapp_support" value="<?php echo htmlspecialchars($valuesRow['whatsapp_support']); ?>" class="form-control" required />
                  </div>
                  
                  <div class="form-group">
                    <label>Withdraw Open Time</label>
                    <input type="time" name="withdrawOpenTime" value="<?php echo htmlspecialchars($valuesRow['withdrawOpenTime']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label>Withdraw Close Time</label>
                    <input type="time" name="withdrawCloseTime" value="<?php echo htmlspecialchars($valuesRow['withdrawCloseTime']); ?>" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label>Auto verify</label>
                    <select name="auto_verify" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['auto_verify'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['auto_verify'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
                  
                  
                  
                     <div class="form-group">
                    <label>UPI Manual verify</label>
                    <select name="verify_upi_payment" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['verify_upi_payment'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['verify_upi_payment'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
            
                     <div class="form-group">
                    <label>Whatsapp in reset</label>
                    <select name="whatsapp_reset" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['whatsapp_reset'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['whatsapp_reset'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
                  
                  
                     <div class="form-group" style="display:none;">
                    <label>GPay</label>
                    <select name="gpay" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['gpay'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['gpay'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
                  
                  
                  
                     <div class="form-group" style="display:none;">
                    <label>PhonePe</label>
                    <select name="phonepe" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['phonepe'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['phonepe'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
                  
                  
                     <div class="form-group" style="display:none;">
                    <label>PayTM</label>
                    <select name="paytm" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['paytm'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['paytm'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
                  
                  
                  
                   <div class="form-group">
                    <label>Razorpay</label>
                    <select name="razorpay" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['razorpay'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['razorpay'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
                  
                     <div class="form-group">
                    <label>OTP Verification</label>
                    <select name="otp_verification" class="form-control">
                        <option value="" selected disabled>Select Globel Setting</option>
                        <option value="1" <?php if($valuesRow['otp_verification'] == 1){ echo 'selected'; }?> >ON</option>
                        <option value="0" <?php if($valuesRow['otp_verification'] == 0){ echo 'selected'; }?> >OFF</option>
                    </select>
                  </div>
                  
                  
                   <div class="form-group">
                    <label>How To Deposite</label>
                    <input type="text" name="how_yo_deposite" value="<?php echo htmlspecialchars($valuesRow['how_yo_deposite']); ?>" class="form-control" required />
                  </div>
                  
                  
                 <div class="form-group">
                    <label>How To Withdraw</label>
                    <input type="text" name="how_to_withdraw" value="<?php echo htmlspecialchars($valuesRow['how_to_withdraw']); ?>" class="form-control" required />
                  </div>
                  
                    <div class="form-group">
                    <label>How To Play</label>
                    <input type="text" name="how_to_play" value="<?php echo htmlspecialchars($valuesRow['how_to_play']); ?>" class="form-control" required />
                  </div>
                  
                  
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" name="AddValues" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div>
            <!-- /.card -->
          </div>
          <?php
              if(isset($_POST['AddValues'])){
                  // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // CSRF token is invalid, display error or handle accordingly
        echo "<script>alert('Invalid CSRF token. Please refresh the page and try again.');</script>";
    } else {              
                
                extract($_REQUEST);
                mysqli_query($con,"update settings set data='$history_delete_days' where data_key='history_delete_days'");
                mysqli_query($con,"update settings set data='$ref_bonus' where data_key='ref_bonus'");
                mysqli_query($con,"update settings set data='$min_deposit' where data_key='min_deposit'");
                mysqli_query($con,"update settings set data='$min_withdraw' where data_key='min_withdraw'");
                mysqli_query($con,"update settings set data='$whatsapp' where data_key='whatsapp'");
                                mysqli_query($con,"update settings set data='$whatsapp_support' where data_key='whatsapp_support'");
                mysqli_query($con,"update settings set data='$max_deposit' where data_key='max_deposit'");

                mysqli_query($con,"update settings set data='$minBalanceAfterWithdraw' where data_key='minBalanceAfterWithdraw'");

                mysqli_query($con,"update settings set data='$upi' where data_key='upi'");
                mysqli_query($con,"update settings set data='$merchant' where data_key='merchant'");
                
                mysqli_query($con,"update settings set data='$upi_2' where data_key='upi_2'");
                mysqli_query($con,"update settings set data='$merchant_2' where data_key='merchant_2'");
                
                mysqli_query($con,"update settings set data='$upi_3' where data_key='upi_3'");
                mysqli_query($con,"update settings set data='$merchant_3' where data_key='merchant_3'");
                
                mysqli_query($con,"update settings set data='$withdrawOpenTime' where data_key='withdrawOpenTime'");
                mysqli_query($con,"update settings set data='$withdrawCloseTime' where data_key='withdrawCloseTime'");
                mysqli_query($con,"update settings set data='$auto_verify' where data_key='auto_verify'");
                
                mysqli_query($con,"update settings set data='$chat_support' where data_key='chat_support'");
                mysqli_query($con,"update settings set data='$telegram' where data_key='telegram'");
                mysqli_query($con,"update settings set data='$telegram_details' where data_key='telegram_details'");
                
                mysqli_query($con,"update settings set data='$whatsapp_active' where data_key='whatsapp_active'");
                mysqli_query($con,"update settings set data='$welcome_msg' where data_key='welcome_msg'");
                mysqli_query($con,"update settings set data='$otp_verification' where data_key='otp_verification'");

                mysqli_query($con,"update settings set data='$verify_upi_payment' where data_key='verify_upi_payment'");

                mysqli_query($con,"update settings set data='$gpay' where data_key='gpay'");
                mysqli_query($con,"update settings set data='$paytm' where data_key='paytm'");
                mysqli_query($con,"update settings set data='$phonepe' where data_key='phonepe'");
                mysqli_query($con,"update settings set data='$whatsapp_reset' where data_key='whatsapp_reset'");
                mysqli_query($con,"update settings set data='$razorpay' where data_key='razorpay'");

                mysqli_query($con,"update settings set data='$signup_reward' where data_key='signup_reward'");
                
                
                mysqli_query($con,"update settings set data='$telegram' where data_key='telegram'");
                
                mysqli_query($con,"update settings set data='$telegram_details' where data_key='telegram_details'");
                
                mysqli_query($con,"update settings set data='$how_yo_deposite' where data_key='how_yo_deposite'");
                 
                mysqli_query($con,"update settings set data='$how_to_withdraw' where data_key='how_to_withdraw'");
                 
                mysqli_query($con,"update settings set data='$how_to_play' where data_key='how_to_play'"); 
                
                $remark = 'Main setting is updated';
                log_action($remark);  // Call the function to log the action
                    
                echo "<script>window.location.href='main-settings.php'</script>";
                
                
            }
              }
          ?>
          <!--/.col (left) -->
          <!-- right column -->
          <!--/.col (right) -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
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

<script>
    $(function () {
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    });
</script>