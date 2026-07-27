<?php include('header.php'); 

if(isset($_POST['submit'])){
  $single_ank = $_POST['single_ank'];
  $triple_pana = $_POST['triple_pana'];
  $createDate = date("Y-m-d H:i:s");
  
  $select = mysqli_query($con, "SELECT *FROM `triple_pana` WHERE `single_ank`='$single_ank' AND `triple_pana`='$triple_pana' ");
  $count = mysqli_num_rows($select);
  
  if($count > 0){
      echo "<script>alert('Already exits. Please try again...!');</script>";
  }else{
      $insert = mysqli_query($con, "INSERT INTO `triple_pana`(`single_ank`, `triple_pana`, `created_at`) VALUES ('$single_ank','$triple_pana','$createDate')");
  
      if($insert){
          echo "<script>window.location.href='triple-pana.php';</script>";
      }
  }
  
}
?>

<style>
  .info-box-icon{
    font-size:25px;
    margin-bottom:10px;
  }
</style>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Triple Pana</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Triple Pana</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                    Add Pana
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <form method="post">
                  <div class="row">
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label>Single Ank</label>
                        <select class="form-control" name="single_ank">
                          <option value="" selected disabled>--Select Single Ank--</option>
                            <?php 
                              $singleAnk = mysqli_query($con, "SELECT *FROM `single_digit` ORDER BY id ASC");
                              while($fetch = mysqli_fetch_array($singleAnk)){
                            ?>
                              <option value="<?php echo $fetch['number']; ?>"><?php echo $fetch['number']; ?></option>
                            <?php
                              }
                            ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-sm-4">
                      <div class="form-group">
                        <label>Triple Pana</label>
                        <input type="number" name="single_pana" min="0" max="999" maxlength="3" pattern="[0-9]{3}" class="form-control" />
                      </div>
                    </div>
                    <div class="col-sm-4">
                      <div class="form-group mt-2">
                        <button type="submit" class="btn btn-primary btn-block mt-4" name="submit">Add</button>
                      </div>
                    </div>
                  </div>
                                        
                </form>
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

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                    <!-- <button class="btn btn-primary">Bid History</button> -->
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <?php
                  $singleDigit = mysqli_query($con, "SELECT * FROM `single_digit`");
                                        
                  while($row = mysqli_fetch_array($singleDigit)){
                    $single = $row['number'];
                ?>
                  <h2 class="bg-danger p-2"><?php echo $single; ?></h2>
                  <div class="row">
                    <?php
                      $singlePana = mysqli_query($con, "SELECT * FROM `triple_pana` WHERE `single_ank`='$single' ORDER BY triple_pana ASC ");
                      while($fetch = mysqli_fetch_array($singlePana)){
                    ?>
                    <div class="col-md-1">
                      <span class="info-box-icon badge badge-primary"><?php echo $fetch['triple_pana']; ?></span>
                    </div>
                    <?php
                      }
                    ?>
                  </div>
                <?php
                  }
                ?>
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

<?php include('footer.php'); ?>
