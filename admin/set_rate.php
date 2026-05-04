<?php include('header.php'); 

$market = $_REQUEST['market'];


    
    if(isset($_POST['submit'])){
        $singleDigit1 = $_POST['singleDigit1'];
       // $singleDigit2 = $_POST['singleDigit2'];
        $jodiDigit1 = $_POST['jodiDigit1'];
        //$jodiDigit2 = $_POST['jodiDigit2'];
        $singlePana1 = $_POST['singlePana1'];
        //$singlePana2 = $_POST['singlePana2'];
        $doublePana1 = $_POST['doublePana1'];
    //    $doublePana2 = $_POST['doublePana2'];
        $triplePana1 = $_POST['triplePana1'];
      //  $triplePana2 = $_POST['triplePana2'];
        $halfSangam1 = $_POST['halfSangam1'];
//        $halfSangam2 = $_POST['halfSangam2'];
        $fullSangam1 = $_POST['fullSangam1'];
  //      $fullSangam2 = $_POST['fullSangam2'];
      
      if(mysqli_num_rows(mysqli_query($con,"select sn from market_rates where market='$market'")) > 0){
        
        mysqli_query($con,"update market_rates set rate='$singleDigit1' where market='$market' AND game='single'");
        mysqli_query($con,"update market_rates set rate='$jodiDigit1' where market='$market' AND game='jodi'");
        mysqli_query($con,"update market_rates set rate='$singlePana1' where market='$market' AND game='singlepatti'");
        mysqli_query($con,"update market_rates set rate='$doublePana1' where market='$market' AND game='doublepatti'");
        mysqli_query($con,"update market_rates set rate='$triplePana1' where market='$market' AND game='triplepatti'");
        mysqli_query($con,"update market_rates set rate='$halfSangam1' where market='$market' AND game='halfsangam'");
        mysqli_query($con,"update market_rates set rate='$fullSangam1' where market='$market' AND game='fullsangam'");
        
      } else {
        
        mysqli_query($con,"INSERT INTO `market_rates`(`market`, `game`, `rate`) VALUES ('$market','single','$singleDigit1')");
        mysqli_query($con,"INSERT INTO `market_rates`(`market`, `game`, `rate`) VALUES ('$market','jodi','$jodiDigit1')");
        mysqli_query($con,"INSERT INTO `market_rates`(`market`, `game`, `rate`) VALUES ('$market','singlepatti','$singlePana1')");
        mysqli_query($con,"INSERT INTO `market_rates`(`market`, `game`, `rate`) VALUES ('$market','doublepatti','$doublePana1')");
        mysqli_query($con,"INSERT INTO `market_rates`(`market`, `game`, `rate`) VALUES ('$market','triplepatti','$triplePana1')");
        mysqli_query($con,"INSERT INTO `market_rates`(`market`, `game`, `rate`) VALUES ('$market','halfsangam','$halfSangam1')");
        mysqli_query($con,"INSERT INTO `market_rates`(`market`, `game`, `rate`) VALUES ('$market','fullsangam','$fullSangam1')");
        
      }
      
       
    }
$select = mysqli_query($con, "SELECT * FROM `market_rates` where market='$market'");
while( $rate = mysqli_fetch_array($select)){
 $rates[$rate['game']] = $rate['rate'];
}
?>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Game Rates</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="Dashboard">Home</a></li>
              <li class="breadcrumb-item active">Game Rates</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

<!-- Main content -->
<section class="content">
      <div class="container-fluid">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <!-- Form Element sizes -->
                <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Game Rates</h3>
                </div>
                <form method="POST">
                    <div class="card-body">
                    <h4>Single Digit</h4>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label class="mb-2 mr-sm-2">Value:</label>
                                            <input type="text" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $rates['single']; ?>" name="singleDigit1" required />
                                        </div>
                                        
                                    </div>
                                    
                                    <h4>Jodi Digit</h4>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label class="mb-2 mr-sm-2">Value:</label>
                                            <input type="text" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $rates['jodi']; ?>" name="jodiDigit1" required />
                                        </div>
                                      
                                    </div>
                                    
                                    <h4>Single Pana</h4>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label class="mb-2 mr-sm-2">Value:</label>
                                            <input type="text" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $rates['singlepatti']; ?>" name="singlePana1" required />
                                        </div>
                                       
                                    </div>
                                    
                                    <h4>Double Pana</h4>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label class="mb-2 mr-sm-2">Value:</label>
                                            <input type="text" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $rates['doublepatti']; ?>" name="doublePana1" required />
                                        </div>
                                       
                                    </div>
                                    
                                    <h4>Triple Pana</h4>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label class="mb-2 mr-sm-2">Value:</label>
                                            <input type="text" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $rates['triplepatti']; ?>" name="triplePana1" required />
                                        </div>
                                       
                                    </div>
                                    
                                    <h4>Half Sangam</h4>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label class="mb-2 mr-sm-2">Value:</label>
                                            <input type="text" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $rates['halfsangam']; ?>" name="halfSangam1" required />
                                        </div>
                                        
                                    </div>
                                    
                                    <h4>Full Sangam</h4>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label class="mb-2 mr-sm-2">Value:</label>
                                            <input type="text" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $rates['fullsangam']; ?>" name="fullSangam1" required />
                                        </div>
                                        
                                    </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
                <!-- /.card-body -->
                </div>
                <!-- /.card -->

            </div>
            <div class="col-md-2"></div>
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->


<?php include('footer.php'); ?>
