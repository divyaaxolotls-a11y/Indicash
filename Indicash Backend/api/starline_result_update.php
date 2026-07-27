<?php
include "con.php";


if (!checks("admin"))
{
    redirect("login.php");
}

$time = date("H:i",$stamp);
$day = strtoupper(date("l",$stamp));
$date = date("d/m/Y");

$sn = $_REQUEST['sn'];



if (isset($_REQUEST['submit']))
{
    extract($_REQUEST);
    
    if(strlen($panna) != 3 && strlen($open) != 1){
        
        $error = "Open must be 1 digit and panna must be 3 digit";
        
    } else {
        
        mysqli_query($con,"INSERT INTO `starline_results`( `market`, `timing`, `panna`, `number`, `date`, `created_at`) VALUES ('$market','$timing','$panna','$open','$date','$stamp')");
        
        $xvm = mysqli_query($con,"select * from rates where sn='1'");
        $xv = mysqli_fetch_array($xvm);
        
        $get_games = mysqli_query($con,"SELECT * FROM `starline_games` where bazar='$market' AND timing_sn='$timing' AND date='$date' AND (number='$panna' OR number='$open' OR number='$jodi')");
        
        while($x = mysqli_fetch_array($get_games))
        {
            $sn = $x['sn'];
            $user = $x['user'];
            $amount = $x['amount']*$xv[$x['game']];
            
            $remrk = $x['game']." ".$x['bazar']." Winning";
        
            mysqli_query($con,"update starline_games set status='1' where sn='$sn'");
        
            mysqli_query($con,"update users set winning=winning+'$amount' where mobile='$user'");
            
            mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `created_at`) VALUES ('$user','$amount','1','$remrk','$stamp')");
        }
        
    }
    
    if(isset($_REQUEST['redirect_back'])){
        header("location:".$redirect_back);
    }
}



$market = mysqli_query($con,"select * from starline_markets where active='1'");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Panel</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="vendors/select2/select2.min.css">
    <link rel="stylesheet" href="vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/horizontal-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/favicon.png" />
</head>

<body>
<div class="container-scroller">
    <!-- partial:../../partials/_horizontal-navbar.html -->
    <?php include "include/header.php"; ?>

    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <div class="main-panel">
            <div class="content-wrapper">
                
                   <?php if(isset($error)) { ?>
                        
                            <div class="alert alert-danger" role="alert">
                              <?php echo $error; ?>
                            </div>
                        
                        <?php } ?>
                        
                <div class="row">
                    
              
                    
                    <?php while($mrk = mysqli_fetch_array($market)){
                        
                        $mrk_sn = $mrk['sn'];
                        
                        $mrk_name = $mrk['name'];
                        
                        $get_timings = mysqli_query($con,"select * from starline_timings where market='$mrk_name'");
                        
                        while($xc = mysqli_fetch_array($get_timings)){
                            
                            
                        $time_id = $xc['close'];
                    
                        if($mrk['days'] == "" || substr_count($mrk['days'],$day) == 0){
                        
                            if(strtotime($time)<strtotime($xc['open'])){
                                $xc['is_open'] = "1";
                            } else {
                                $xc['is_open'] = "0";
                            }
                            
                            if(strtotime($time)<strtotime($xc['close'])) {
                                $xc['is_close'] = "1";
                            } else {
                                $xc['is_close'] = "0";
                            }
                            
                        } else if(substr_count($xc['days'],$day."(CLOSE)") > 0){
                            $xc['is_open'] = "0";
                            $xc['is_close'] = "0";
                        }
                        
                        
                        if($xc['is_close'] == "0" && mysqli_num_rows(mysqli_query($con,"select sn from starline_results where date='$date' AND market='$mrk_name' AND timing='$time_id'")) == 0){
                            
                            $available = true;
                    
                    ?>

                    <div class="col-sm-12 col-md-6 col-lg-4 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title"><?php echo $mrk['name'].' ( '.$xc['close'].' )'; ?></h4>
                                
                                
                                <form class="forms-sample" method="post" enctype="multipart/form-data">
                                     
                                     
                                    <input type="hidden" name="market" value="<?php echo $mrk['name']; ?>" required>
                                    
                                    <input type="hidden" name="timing" value="<?php echo $xc['close']; ?>" required>
                                    
                                   <div class="row">
                                       <div class="col-sm-8">
                                           <div class="form-group">
                                                <label for="panna">Panna</label>
                                                <input type="text" pattern="(.){3,3}"  maxlength="3" class="form-control" id="panna" name="panna" required>
                                            </div>
                                       </div>
                                       <div class="col-sm-4">
                                           <div class="form-group">
                                                <label for="open">Open</label>
                                                <input type="text" pattern="(.){1,1}"  maxlength="1"  class="form-control" id="open" name="open" required>
                                            </div>
                                       </div>
                                       
                                        <div class="col-sm-4">
                                           <div class="form-group">
                                                <label for="open">Jodi</label>
                                                <input type="text" pattern="(.){2,2}"  maxlength="2"  class="form-control" id="jodi" name="jodi" required>
                                            </div>
                                       </div>
                                   </div>
                                    
                                    
                                    
                                    <button type="submit" class="btn btn-primary mr-2 mt-4" name="submit" style="width: 100%">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <?php } } }
                    
                    if(!isset($available)){ ?>
                        
                        <h2 style="text-align: center;
    position: relative;
    width: 100%;">No Results Pending</h2>
                        
                   <?php }
                    
                    ?>

                </div>
            </div>
            <!-- content-wrapper ends -->
            <!-- partial:../../partials/_footer.html -->
            <footer class="footer">
                <div class="w-100 clearfix">
                    <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i class="ti-heart text-danger ml-1"></i></span>
                </div>
            </footer>
            <!-- partial -->
        </div>
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->
<!-- plugins:js -->
<script src="vendors/js/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- Plugin js for this page -->
<script src="vendors/typeahead.js/typeahead.bundle.min.js"></script>
<script src="vendors/select2/select2.min.js"></script>
<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<script src="js/settings.js"></script>
<script src="js/todolist.js"></script>
<!-- endinject -->
<!-- Custom js for this page-->
<script src="js/file-upload.js"></script>
<script src="js/typeahead.js"></script>
<script src="js/select2.js"></script>
<!-- End custom js for this page-->

<script src="https://cdn.tiny.cloud/1/6n9e3b6bbutqzcha0os8jsggfbmiqxiy166ekcaclp6aw530/tinymce/5/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: 'textarea',  // change this value according to your HTML
        auto_focus: 'element1'
    });
</script>

</body>

</html>
