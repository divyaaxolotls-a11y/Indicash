<?php include('header.php'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Bid History</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Bid History</li>
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
                    Details
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                  
                    <th>#</th>
                    <th>User</th>
                                        <th>Bid TXID</th>

                    <th>Mobile Number</th>
                    <th>Game</th>
                    <th>Bazar</th>
                    <th>Number</th>
                    <th>Amount</th>
                     <th>Date</th>
                  </tr>
                  </thead>
                  <tbody>
                    <?php
                    $num_results_on_page = 10;  
                    $page = isset($_GET['page']) ? $_GET['page'] : 1;
                    $start_from = ($page-1) * $num_results_on_page;  
                    
                    // Fetching only required columns from 'games' table
                    
                    //  $result = mysqli_query($con,"SELECT sn, user, game, bazar, amount, date FROM games LIMIT $start_from, $num_results_on_page");
                    // Get today's date in 'd/m/Y' format
                       $todayDate = date('d/m/Y');
                    // $result = mysqli_query($con,"SELECT sn,user, game,game_type,number, bazar, amount, date FROM games WHERE date = '$todayDate' LIMIT $start_from, $num_results_on_page");
                    
                    
                    $result = mysqli_query($con, "
    SELECT g.*,u.name
    FROM games g
    JOIN users u ON g.user = u.mobile
    WHERE g.date = '$todayDate'
    ORDER BY g.sn DESC

");

                    
                    
                   $i = $start_from + 1; 
                    while ($row = mysqli_fetch_array($result)) { 
                    ?>
                            <tr>
                               
                                <td><?php echo $i; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                                                <td><?php echo $row['sn']; ?></td>

                                <td><?php echo $row['user']; ?></td>
                                <td><?php echo $row['game_type']; ?></td>
                                <td><?php echo $row['bazar']; ?></td>
                                 <td><?php echo $row['number']; ?></td>
                                <td><?php echo $row['amount']; ?></td>
                                <td><?php echo $row['date']; ?></td>
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

