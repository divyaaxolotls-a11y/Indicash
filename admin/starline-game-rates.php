<?php 
include('header.php');

$select = mysqli_query($con, "SELECT * FROM `starline_game_rates` ");
$row = mysqli_fetch_array($select);
    
    if(isset($_POST['submit'])){
        $singleDigit1 = $_POST['singleDigit1'];
        $singleDigit2 = $_POST['singleDigit2'];
        $singlePana1 = $_POST['singlePana1'];
        $singlePana2 = $_POST['singlePana2'];
        $doublePana1 = $_POST['doublePana1'];
        $doublePana2 = $_POST['doublePana2'];
        $triplePana1 = $_POST['triplePana1'];
        $triplePana2 = $_POST['triplePana2'];
        
        
        $update = mysqli_query($con, "UPDATE `starline_game_rates` SET 
                                            `single_digit_value1`='$singleDigit1',
                                            `single_digit_value2`='$singleDigit2',
                                            `single_pana_value1`='$singlePana1',
                                            `single_pana_value2`='$singlePana2',
                                            `double_pana_value1`='$doublePana1',
                                            `double_pana_value2`='$doublePana2',
                                            `triple_pana_value1`='$triplePana1',
                                            `triple_pana_value2`='$triplePana2'
                                            WHERE `id`='1' ");
        if($update){
            echo "<script>window.location.href='starline-game-rates.php';</script>";
        }
    }

?>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Starline Game Rates</h1>
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
                                <label class="mb-2 mr-sm-2">Value 1:</label>
                                <input type="number" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $row['single_digit_value1']; ?>" name="singleDigit1" required />
                            </div>
                            <div class="col-sm-6">
                                <label for="pwd2" class="mb-2 mr-sm-2">Value 2:</label>
                                <input type="number" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $row['single_digit_value2']; ?>" name="singleDigit2" required />
                            </div>
                        </div>
                                    
                        <h4>Single Pana</h4>
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="mb-2 mr-sm-2">Value 1:</label>
                                <input type="number" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $row['single_pana_value1']; ?>" name="singlePana1" required />
                            </div>
                            <div class="col-sm-6">
                                <label for="pwd2" class="mb-2 mr-sm-2">Value 2:</label>
                                <input type="number" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $row['single_pana_value2']; ?>" name="singlePana2" required />
                            </div>
                        </div>
                                    
                        <h4>Double Pana</h4>
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="mb-2 mr-sm-2">Value 1:</label>
                                <input type="number" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $row['double_pana_value1']; ?>" name="doublePana1" required />
                            </div>
                            <div class="col-sm-6">
                                <label for="pwd2" class="mb-2 mr-sm-2">Value 2:</label>
                                <input type="number" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $row['double_pana_value2']; ?>" name="doublePana2" required />
                            </div>
                        </div>
                                    
                        <h4>Triple Pana</h4>
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="mb-2 mr-sm-2">Value 1:</label>
                                <input type="number" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $row['triple_pana_value1']; ?>" name="triplePana1" required />
                            </div>
                            <div class="col-sm-6">
                                <label for="pwd2" class="mb-2 mr-sm-2">Value 2:</label>
                                <input type="number" class="form-control mb-2 mr-sm-2" min="0" value="<?php echo $row['triple_pana_value2']; ?>" name="triplePana2" required />
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
