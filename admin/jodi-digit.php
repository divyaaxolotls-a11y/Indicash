<?php include('header.php'); ?>

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
            <h1>Jodi Digits</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Jodi Digits</li>
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
              <div class="card-header">
                <h3 class="card-title">
                    <!-- <button class="btn btn-primary">Bid History</button> -->
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="row">
                  <?php
                    $singleDigit = mysqli_query($con, "SELECT * FROM `jodi_digit`");
                                        
                    while($row = mysqli_fetch_array($singleDigit)){
                  ?>
                  <div class="col-md-1">
                    <span class="info-box-icon badge badge-primary"><?php echo $row['number']; ?></span>
                  </div>
                  <?php
                    }
                  ?>
                </div>
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
