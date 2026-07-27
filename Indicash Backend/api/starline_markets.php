<?php
include "con.php";

if (!checks("admin"))
{
    redirect("login.php");
}

if(isset($_REQUEST['delete'])){
    $sn = $_REQUEST['delete'];
    
    mysqli_query($con,"delete from starline_markets where sn='$sn'");
}


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
    <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/horizontal-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="images/favicon.png" />
</head>

<body>
<div class="container-scroller">
    <!-- partial:partials/_horizontal-navbar.html -->
    <?php include "include/header.php"; ?>

    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <div class="main-panel">
            <div class="content-wrapper">
                
             
                
                <div class="card">
                    
                       <a style="position: absolute;right: 20px;margin-top: 18px;" href="starline_add_market.php"><button class="btn btn-primary">Add New Market</button></a>
                       
                    <div class="card-body" style="margin-top: 25px;">
                        <h4 class="card-title">Select Market</h4>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="order-listing" class="table">
                                            <thead>
                                                <tr>
                                                    <th>Sn</th>
                                                    <th>Image</th>
                                                    <th>Name</th>
                                                    <th>Days</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                        <tbody>
                                            
                                            <?php
                                            $i = 1;
                                            $get = mysqli_query($con,"select * from starline_markets");
                                            while($xc = fetch($get))
                                            { ?>
                                            
                                            
                                            
                                            <tr>
                                                <td><?php echo $i; $i++; ?></td>
                                                <td><a href="<?php echo $xc['image']; ?>" target='_blank'><img src="<?php echo $xc['image']; ?>"></a></td>
                                              
                                                <td><?php echo $xc['name']; ?></td>
                                                <td><?php echo $xc['days']; ?></td>
                                                <td>
                                                    <a href="starline_markets.php?delete=<?php echo $xc['sn']; ?>"> <button class="btn btn-outline-info" onclick="return confirm('Are you sure you want to remove this market')">Delete</button> </a>
                                                    
                                                    <a href="starline_timings.php?sn=<?php echo $xc['name']; ?>"> <button class="btn btn-outline-info">Manage Timings</button> </a>
                                                    
                                                    <a href="starline_bets.php?sn=<?php echo $xc['name']; ?>"> <button class="btn btn-outline-info">View Bets</button> </a>
                                                    
                                                    <a href="../api/chart/getChart.php?market=<?php echo $xc['name']; ?>"> <button class="btn btn-outline-info">View Chart</button> </a>
                                                </td>
                                            </tr>
                                            
                                            
                                            
                                            <?php } ?>
                                            
                                           

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
            <!-- partial:partials/_footer.html -->
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
<!-- Plugin js for this page-->
<script src="vendors/datatables.net/jquery.dataTables.js"></script>
<script src="vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
<!-- End plugin js for this page-->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<script src="js/settings.js"></script>
<script src="js/todolist.js"></script>
<!-- endinject -->
<!-- Custom js for this page-->
<script src="js/data-table.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.6/dist/loadingoverlay.min.js"></script>
<!-- End custom js for this page-->
</body>

</html>
