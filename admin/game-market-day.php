<?php include('header.php'); 

$gameID = $_GET['gameID'];
$game = mysqli_query($con, "SELECT * FROM `game_list` WHERE `id`='$gameID' ");
$fetch = mysqli_fetch_array($game);

if(isset($_POST['submit'])){
    $day = $_POST['day'];
    $status = $_POST['status'];
    $openTime = $_POST['openTime'];
    $OpenTime24  = date("H:i", strtotime($openTime));
    $closeTime = $_POST['closeTime'];
    $CloseTime24  = date("H:i", strtotime($closeTime));
    $CreateDate = date("Y-m-d H:i:s");
    
    $marketSelect = mysqli_query($con, "SELECT * FROM `game_week_day` WHERE `game_id`='$gameID' AND `day`='$day' ");
    $count = mysqli_num_rows($marketSelect);
    
    if($count > 0){
        $update = mysqli_query($con, "UPDATE `game_week_day` SET `openTime`='$OpenTime24',`closeTime`='$CloseTime24',`status`='$status' WHERE `game_id`='$gameID' AND `day`='$day'");
    
        if($update){
            echo "<script>window.location.href='';</script>";
        }
    }else{
    
        $insert = mysqli_query($con, "INSERT INTO `game_week_day`(`game_id`, `day`, `openTime`, `closeTime`, `status`, `created_at`) 
                                                    VALUES ('$gameID','$day','$OpenTime24','$CloseTime24','$status','$CreateDate')");
        if($insert){
            echo "<script>window.location.href='';</script>";
        }
    }
    
}

?>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1><?php echo $fetch['game_name']; ?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="Dashboard">Home</a></li>
              <li class="breadcrumb-item active"><?php echo $fetch['game_name']; ?></li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Filters</h3>

                    <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <form method="post">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                <label>Day</label>
                                <select class="form-control select2bs4" name="day" style="width: 100%;">
                                    <option selected disabled value="">Select Day</option>
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                    <option value="sunday">Sunday</option>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" style="width: 100%;">
                                        <option selected disabled value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Open Time</label>
                                    <input type="time" class="form-control" value="<?php echo $fetch['open_time']; ?>" name="openTime" required/>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Close Time</label>
                                    <input type="time" class="form-control" value="<?php echo $fetch['close_time']; ?>" name="closeTime" required/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                <br>
                                    <button type="submit" name="submit" class="btn btn-primary btn-block">SAVE</button>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                </form>
            </div>
            <!-- /.card -->

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
                    <button class="btn btn-primary">All Days</button>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                        <th>#</th>
                        <th>Day</th>
                        <th>Open Time</th>
                        <th>Close Time</th>
                        <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                        $market = mysqli_query($con, "SELECT * FROM `game_week_day` WHERE `game_id`='$gameID' ORDER BY id ASC");
                        $i=1;
                        while($row = mysqli_fetch_array($market)){
                    ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td style="text-transform: capitalize;"><?php echo $row['day']; ?></td>
                        <td><?php echo date("h:i:s a", strtotime($row['openTime'])); ?></td>
                        <td><?php echo date("h:i:s a", strtotime($row['closeTime'])); ?></td>
                        <td>
                            <?php  
                                if($row['status'] == 1){
                                                    
                            ?>
                                <span class="badge badge-success">Active</span>
                                <?php
                                    }else{
                                ?>
                                <span class="badge badge-danger">Deactive</span>
                            <?php
                                }
                            ?>
                        </td>
                    </tr>
                    <?php
                        $i++;
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

<?php include('footer.php'); ?>
<script>
    $(function () {
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    });
</script>