<?php include('header.php');

if (in_array(1, $HiddenProducts)){
                             

// if($_SESSION['userID'] == 'admin@gmail.com'){

if(isset($_REQUEST['complete'])){
    $sn = htmlspecialchars($_REQUEST['complete'], ENT_QUOTES, 'UTF-8'); // Escape the 'sn' parameter
    $info = mysqli_fetch_array(mysqli_query($con,"select user, amount from upi_verification where sn='$sn'"));
    $mobile = $info['user'];
    $amount = $info['amount'];
    
   mysqli_query($con,"delete from upi_verification where sn='$sn'");
    
    mysqli_query($con,"update users set wallet=wallet+'$amount' where mobile='$mobile'");

    mysqli_query($con,"INSERT INTO `transactions`( `user`, `amount`, `type`, `remark`, `owner`, `created_at`) VALUES ('$mobile','$amount','1','Deposit','user','$stamp')");
    
    header('location:dashboard.php');
}


if(isset($_REQUEST['cancel'])){
    $sn = $_REQUEST['cancel'];
    
    mysqli_query($con,"delete from upi_verification where sn='$sn'");
    
    header('location:dashboard.php');
}


?>


<?php 

// Ensure session user is valid
if ($_SESSION['userID'] == 'admin@gmail.com') {

    // Handle 'complete' request
    if (isset($_REQUEST['complete'])) {
        $sn = htmlspecialchars($_REQUEST['complete'], ENT_QUOTES, 'UTF-8'); // Escape the 'sn' parameter
        
        // Validate that 'sn' is numeric
        if (!is_numeric($sn)) {
            echo "<script>alert('Invalid request.'); window.location.href='dashboard.php';</script>";
            exit;
        }
        
        // Fetch information from the database
        $info = mysqli_fetch_array(mysqli_query($con, "SELECT user, amount FROM upi_verification WHERE sn='$sn'"));

        // Check if the record exists
        if ($info) {
            $mobile = $info['user'];
            $amount = $info['amount'];

            // Ensure 'amount' is numeric
            if (!is_numeric($amount)) {
                echo "<script>alert('Invalid amount.'); window.location.href='dashboard.php';</script>";
                exit;
            }

            // Prepare and execute database queries using prepared statements
            $deleteQuery = "DELETE FROM upi_verification WHERE sn=?";
            $stmt = $con->prepare($deleteQuery);
            $stmt->bind_param("i", $sn);
            $stmt->execute();
            $stmt->close();

            // Update wallet balance
            $updateWalletQuery = "UPDATE users SET wallet = wallet + ? WHERE mobile = ?";
            $stmt = $con->prepare($updateWalletQuery);
            $stmt->bind_param("ds", $amount, $mobile); // 'd' for double, 's' for string
            $stmt->execute();
            $stmt->close();

            // Insert transaction record
            $transactionQuery = "INSERT INTO transactions (user, amount, type, remark, owner, created_at) 
                                 VALUES (?, ?, '1', 'Deposit', 'user', ?)";
            $stmt = $con->prepare($transactionQuery);
            $createdAt = date("Y-m-d H:i:s"); // Ensure the correct format for created_at
            $stmt->bind_param("sds", $mobile, $amount, $createdAt);
            $stmt->execute();
            $stmt->close();

            // Redirect back to the dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            // If no such record exists
            echo "<script>alert('Record not found.'); window.location.href='dashboard.php';</script>";
        }
    }

    // Handle 'cancel' request
    if (isset($_REQUEST['cancel'])) {
        $sn = htmlspecialchars($_REQUEST['cancel'], ENT_QUOTES, 'UTF-8'); // Escape the 'sn' parameter

        // Validate that 'sn' is numeric
        if (!is_numeric($sn)) {
            echo "<script>alert('Invalid request.'); window.location.href='dashboard.php';</script>";
            exit;
        }

        // Prepare and execute delete query using prepared statements
        $deleteQuery = "DELETE FROM upi_verification WHERE sn=?";
        $stmt = $con->prepare($deleteQuery);
        $stmt->bind_param("i", $sn);
        $stmt->execute();
        $stmt->close();

        // Redirect back to the dashboard
        header('Location: dashboard.php');
        exit;
    }
}

?>


<!-- Content Header (Page header) -->
<div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->

    <!--<section class="content-header">-->
    <!--  <div class="container-fluid">-->
    <!--    <div class="row mb-2">-->
    <!--      <div class="col-sm-6">-->
    <!--        <h1>Sell Report</h1>-->
    <!--      </div>-->
    <!--      <div class="col-sm-6">-->
    <!--        <ol class="breadcrumb float-sm-right">-->
    <!--          <li class="breadcrumb-item"><a href="Dashboard">Home</a></li>-->
    <!--          <li class="breadcrumb-item active">Sell Report</li>-->
    <!--        </ol>-->
    <!--      </div>-->
    <!--    </div>-->
    <!--  </div>
    <!-- /.container-fluid -->
    <!--</section>-->

     <!--Main content -->
    <section class="content">
        <div class="container-fluid">
             <!--SELECT2 EXAMPLE -->
    <!--<div class="card card-default">-->
                <!--<div class="card-header">-->
                    <!--<h3 class="card-title">Filters</h3>-->

                    <!--<div class="card-tools">-->
                    <!--<button type="button" class="btn btn-tool" data-card-widget="collapse">-->
                    <!--    <i class="fas fa-minus"></i>-->
                    <!--</button>-->
                    <!--<button type="button" class="btn btn-tool" data-card-widget="remove">-->
                    <!--    <i class="fas fa-times"></i>-->
                    <!--</button>-->
                    <!--</div>-->
                <!--</div>-->
                 <!--/.card-header -->
                <!--<div class="card-body">-->
                        
                <!--    <div class="row">-->
                        <!--<div class="col-md-2">-->
                        <!--    <div class="form-group">-->
                        <!--        <label>Date</label>-->
                        <!--        <input type="date" name="date" id="resultDate" value="<?php echo date('Y-m-d'); ?>" class="form-control" />-->
                        <!--        <input type="hidden" name="refcodeq" id="refCodeq" value="<?php echo $refcodeq ?>" class="form-control" />-->
                        <!--        <input type="hidden" name="iDd" id="iDd" value="<?php echo $idd ?>" class="form-control" />-->
                        <!--    </div>-->
                        <!--</div>-->
                        
                        
                        
                        
                        
                        <div class="col-md-3">
                            <div class="form-group">
                            <!--<label>Game</label>-->
                            <!--<select id="gameId" name="market" class="form-control select2bs4" style="width: 100%;">-->
                                <!--<option value="" selected disabled>Select Game</option>-->
                                <?php
                                    $game = mysqli_query($con,  "SELECT * FROM `gametime_new` ORDER BY market DESC");
                                    $i = 1;
                                    $currentDate = date('Y-m-d');
                                    while($row = mysqli_fetch_array($game)){
                                       
                                    $xc = getOpenCloseTiming($row);
                                       
                                ?>
                                    <!--<option value="<?php echo $row['market']; ?>"><?php echo $row['market']; ?> (<?php echo $xc['open'].' - '.$xc['close']; ?>)</option>-->
                                <?php
                                                        
                                        
                                    $i++;
                                    }
                                ?>
                                <?php
                                    $game = mysqli_query($con,  "SELECT * FROM `gametime_manual` ORDER BY market DESC");
                                    $i = 1;
                                    $currentDate = date('Y-m-d');
                                    while($row = mysqli_fetch_array($game)){
                                       
                                       
                                    $xc = getOpenCloseTiming($row);
                                ?>
                                    <!--<option value="<?php echo $row['market']; ?>"><?php echo $row['market']; ?> (<?php echo $xc['open'].' - '.$xc['close']; ?>)</option>-->
                                <?php
                                                        
                                        
                                    $i++;
                                    }
                                ?>
                            <!--</select>-->
                        <!--    </div>-->
                        <!--</div>-->
                        
                        
                        <!--<div class="col-md-2">-->
                        <!--    <div class="form-group">-->
                        <!--        <label>Session</label>-->
                        <!--        <select id="session" name="session" class="form-control" style="width: 100%;">-->
                        <!--            <option value="" selected disabled>Select Session</option>-->
                        <!--            <option value="open">Open</option>-->
                        <!--            <option value="close">Close</option>-->
                        <!--        </select>-->
                        <!--    </div>-->
                        <!--</div>-->
                        
                        <!--<div class="col-md-2">-->
                        <!--    <div class="form-group">-->
                        <!--        <label>Game Type</label>-->
                        <!--        <select id="type" name="type" class="form-control" style="width: 100%;">-->
                        <!--            <option value="" selected disabled>Select Session</option>-->
                        <!--            <option value="all">All</option>-->
                        <!--            <option value="single">Single</option>-->
                        <!--            <option value="jodi">Jodi</option>-->
                        <!--            <option value="panna">Panna</option>-->
                        <!--        </select>-->
                        <!--    </div>-->
                        <!--</div>-->
                     
                     
                        <!--<div class="col-md-2">-->
                        <!--    <div class="form-group mt-2">-->
                              
                        <!--          <button type="button" id="go"class="btn btn-primary mt-4">Submit</button>-->
                                  
                        <!--      </div>-->
                        <!--    </div>-->
                        </div>
                    </div>
                     <!--/.row -->
    <!--</div>-->
         
            <!-- /.card -->

            <!-- /.card -->
        </div>
    </section>
    
    <style>
                .game_title {
                    width: 100%;
    text-align: center;
    color: #f73d3d;
    border: dashed 1px #000;
    padding: 9px;
                }
      .colls .col-sm {
       width:10% 
      }
      @media only screen
and (max-width : 740px) {
 .titls {
        display:none;
      }
}
     .card-body {
    -webkit-flex: 1 1 auto;
    -ms-flex: 1 1 auto;
    flex: 1 1 auto;
    min-height: 1px;
    padding: 0.25rem;
}
                    .colls p {
                        margin-bottom:0px;
    font-size: 19px;
    border: solid 1px #000;    
    padding: 5px 0px;
    text-align: center;
                    }
                    .colls .row {
                        margin-left:0px;
                        margin-right:0px;
                    }
                    
                    .colls .col-sm {
                        margin-left:0px;
                        margin-right:0px;
                        padding-right: 0px;
                         padding-left: 0px;

                    }
                    
                    .bluebox {
                            background: blue;    padding-left: 7px;
                            padding-right: 7px;
                            border-radius: 7px;
                            color: white;
                    }
                    .redbox {
                        background: red;
                         padding-left: 7px;
                         padding-right: 7px;
                         border-radius: 7px;
                         color: white;
                    }
                </style>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              
              <!-- /.card-header -->
              <!--<div class="card-body" id="result_data">-->
               
            
              <!--</div>-->
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
    
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            
          <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>
                    <?php 
                        $approverUsers = mysqli_query($con, "SELECT * FROM `users` WHERE `verify`='1' ");
                        echo $count = mysqli_num_rows($approverUsers);
                    ?>
                </h3>

                <p>Approved Users</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="users_old.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>
                    <?php 
                        $approverUsers = mysqli_query($con, "SELECT * FROM `users` WHERE `verify`='0' ");
                        echo $count = mysqli_num_rows($approverUsers);
                    ?>
                </h3>

                <p>Un-Approved Users</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="users_old.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>
                    <?php
                        $date = date('d/m/Y');
                        $TotalBidAmt = mysqli_query($con, "SELECT SUM(amount) AS TotalPoints FROM `games` WHERE `date`='$date' ");
                        $fetchTotalPoints = mysqli_fetch_array($TotalBidAmt);
                        
                        $TotalBidAmt2 = mysqli_query($con, "SELECT SUM(amount) AS TotalPoints FROM `starline_games` WHERE `date`='$date' ");
                        $fetchTotalPoints2 = mysqli_fetch_array($TotalBidAmt2);
                        
                        if($fetchTotalPoints['TotalPoints'] != ''){
                            echo $fetchTotalPoints['TotalPoints']+$fetchTotalPoints2['TotalPoints'];
                        }else{
                            echo 0+$fetchTotalPoints2['TotalPoints'];
                        }
                    ?>
                    <sup style="font-size: 20px">₹</sup>
                </h3>

                <p>Today's Bid Amount</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="bids-history.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>
                       
           <?php
  
               $date = date('Y-m-d');
               $totalAmountQuery = mysqli_query($con, "SELECT SUM(amount) AS total_amount FROM `games`");
               $row = mysqli_fetch_array($totalAmountQuery);
               echo  (isset($row['total_amount']) ? $row['total_amount'] : 0);
           ?> 
     
      
                 <sup style="font-size: 20px">₹</sup>
                </h3>

                <p>Total Bid Amount</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="bid-history.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>   
                    
         
                    <?php
                        $date = date('d/m/Y');
                        $NoOfBid = mysqli_query($con, "SELECT * FROM `gametime_manual`");
                      //  $NoOfBid2 = mysqli_query($con, "SELECT * FROM `starline_games` WHERE `date`='$date' ");
                        echo $NofB = mysqli_num_rows($NoOfBid)+0;
                    ?>
                </h3>

                <p>Number Of Markets</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="game-list.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <section class="col-lg-12 connectedSortable">
            
            <div class="card bg-gradient-warning">
              <div class="card-header border-0">
                <h3 class="card-title">
                  <i class="fas fa-map-marker-alt mr-1"></i>
                  Market Bid Details
                </h3>
                <!-- card tools -->
                <div class="card-tools">
                  <button type="button" class="btn btn-primary btn-sm daterange" title="Date range">
                    <i class="far fa-calendar-alt"></i>
                  </button>
                  <button type="button" class="btn btn-primary btn-sm" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
                <!-- /.card-tools -->
              </div>
      
              
              
              <div class="card-body">
    <div class="form-group">
        <label for="gameID">Game Name</label>
        <select id="gameID" class="form-control">
            <option value="" selected disabled>Select Game</option>
            <option value="0">All Games</option>
            <?php 
                // First Query: Select games from 'gametime_new'
                $gameList = mysqli_query($con, "SELECT * FROM `gametime_new` WHERE `active`='1' ORDER BY sn DESC");
                while($row = mysqli_fetch_array($gameList)){
                    // Sanitize output using htmlspecialchars to prevent XSS
                    $market = htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8');
            ?>
            <option value="<?php echo $market; ?>"><?php echo $market; ?></option>
            <?php
                }
            ?>

            <?php 
                // Second Query: Select games from 'gametime_manual'
                $gameList = mysqli_query($con, "SELECT * FROM `gametime_manual` WHERE `active`='1' ORDER BY sn DESC");
                while($row = mysqli_fetch_array($gameList)){
                    // Sanitize output using htmlspecialchars to prevent XSS
                    $market = htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8');
            ?>
            <option value="<?php echo $market; ?>"><?php echo $market; ?></option>
            <?php
                }
            ?>
        </select>
        <br>
        <h4 class="text-center">
            <span class="badge badge-primary" id="bidAmount">0</span>
        </h4>
        <h5 class="text-center">Market Amount</h5>
    </div>
</div>

              <!-- /.card-body-->
            </div>
            
            
            
   <!--Single Ank Bids-->
<div class="card bg-gradient-success">
    <div class="card-header border-0">
        <h3 class="card-title">
            <i class="fas fa-map-marker-alt mr-1"></i>
            Total Bids On Single Ank Of Date <?php echo date('d-M-Y'); ?>
        </h3>
        <!-- card tools -->
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
        <!-- /.card-tools -->
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="game_id">Game Name</label>
                    <select id="game_id" class="form-control">
                        <option value="" selected disabled>Select Game</option>
                        <?php 
                            // First query for gametime_new table
                            $gameList = mysqli_query($con, "SELECT * FROM `gametime_new` WHERE `active`='1' ORDER BY sn DESC");
                            while($row = mysqli_fetch_array($gameList)){
                                // Escape output to prevent XSS
                                $market = htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <option value="<?php echo $market; ?>"><?php echo $market; ?></option>
                        <?php
                            }
                        ?>
                        <?php 
                            // Second query for gametime_manual table
                            $gameList = mysqli_query($con, "SELECT * FROM `gametime_manual` WHERE `active`='1' ORDER BY sn DESC");
                            while($row = mysqli_fetch_array($gameList)){
                                // Escape output to prevent XSS
                                $market = htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <option value="<?php echo $market; ?>"><?php echo $market; ?></option>
                        <?php
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label for="session">Session</label>
                    <select id="session" class="form-control">
                        <option value="" selected disabled>Select Session</option>
                        <option value="open">Open Market</option>
                        <option value="close">Close Market</option>
                    </select>
                </div>
            </div>

            <div class="col-sm-4">
                <br>
                <div class="form-group">
                    <button class="btn btn-primary btn-block" id="getDetails" type="button">Get Details</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.card-body-->
</div>

            
            <div class="row row-cols-5" id="singleAnks">
                    
            </div>
    
          </section>
          <!-- /.Left col -->
         
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
    
    <section class="content">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                    <button class="btn btn-primary">Pending Deposit Request</button>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1z" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                     <th>#</th>
                    <th>Mobile</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Pay ID</th>
                    <th>Created at</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                    

                    <?php
                                        
                                        
                                        
                    $num_results_on_page = 10;  
                    if (isset($_GET["page"])) {
                    	$page  = $_GET["page"]; 
                    } 
                    else{ 
                    	$page=1;
                    };  
                    
                    $start_from = ($page-1) * $num_results_on_page;  
                    
                    $search_url_add = "";
                    
                    $result = mysqli_query($con,"select * from auto_deposits where status = '0' order by sn desc LIMIT $start_from, $num_results_on_page");
                        
                    $result_db = mysqli_query($con,"SELECT COUNT(sn) FROM auto_deposits"); 
                    
                    $action_url = "&page=".$page.$search_url_add;
                    
                    
                    $row_db = mysqli_fetch_row($result_db);  
                    $total_pages = $row_db[0];  
                    
                    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
                    

                    
                     $i = (($page-1)*10)+1; while ($xc = mysqli_fetch_array($result)) { 
                    

                      $mobile = $xc['mobile'];
                      $uinfo = mysqli_fetch_array(mysqli_query($con,"select name from users where mobile='$mobile'"));

                    ?>



                    <tr>
                      <td><?php echo $i; $i++; ?></td>
                      <td><a href="user-profile.php?userID=<?php echo htmlspecialchars($idd); ?>"><?php echo htmlspecialchars($mobile); ?><i class="mdi mdi-link"></i></td>
                      
                      <td><?php echo htmlspecialchars($uinfo['name']); ?></td>
                      <td><?php echo htmlspecialchars($xc['amount']); ?></td>
                      <td><?php echo htmlspecialchars($xc['pay_id']); ?></td>

                      <td><?php echo htmlspecialchars($xc['created_at']); ?></td>
                      <td>
                        <!--<a href="dashboard.php?complete=<?php echo $xc['sn']; ?>"> <button class="btn btn-outline-info" onclick="return confirm('Are you sure you want to proceed')">Completed</button> </a>-->
                        <!--<a href="dashboard.php?cancel=<?php echo $xc['sn']; ?>"> <button class="btn btn-outline-info" onclick="return confirm('Are you sure you want to proceed')">Cancel</button> </a>-->
<a href="auto_deposite_display.php"> <button class="btn btn-outline-info">More</button> </a>

                      </td>
                    </tr>



                    <?php } ?>
                    
                    
                  </tbody>
                </table>
                
                
                <?php if (ceil($total_pages / $num_results_on_page) > 0): 
                                    
                                     ?>
                <ul class="pagination">
                  <?php if ($page > 1): ?>
                  <li class="prev page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page-1 ?><?php echo $search_url_add; ?>">Prev</a></li>
                  <?php endif; ?>

                  <?php if ($page > 3): ?>
                  <li class="start page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=1<?php echo $search_url_add; ?>">1</a></li>
                  <li class="dots page-item">...</li>
                  <?php endif; ?>

                  <?php if ($page-2 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page-2 ?><?php echo $search_url_add; ?>"><?php echo $page-2 ?></a></li><?php endif; ?>
                  <?php if ($page-1 > 0): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page-1 ?><?php echo $search_url_add; ?>"><?php echo $page-1 ?></a></li><?php endif; ?>

                  <li class="currentpage page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page ?><?php echo $search_url_add; ?>"><?php echo $page ?></a></li>

                  <?php if ($page+1 < ceil($total_pages / $num_results_on_page)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page+1 ?><?php echo $search_url_add; ?>"><?php echo $page+1 ?></a></li><?php endif; ?>
                  <?php if ($page+2 < ceil($total_pages / $num_results_on_page)+1): ?><li class="page page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page+2 ?><?php echo $search_url_add; ?>"><?php echo $page+2 ?></a></li><?php endif; ?>

                  <?php if ($page < ceil($total_pages / $num_results_on_page)-2): ?>
                  <li class="dots page-item">...</li>
                  <li class="end page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo ceil($total_pages / $num_results_on_page) ?><?php echo $search_url_add; ?>"><?php echo ceil($total_pages / $num_results_on_page) ?></a></li>
                  <?php endif; ?>

                  <?php if ($page < ceil($total_pages / $num_results_on_page)): ?>
                  <li class="next page-item"><a class='page-link' href="<?php echo $_PHP_SELF; ?>?page=<?php echo $page+1 ?><?php echo $search_url_add; ?>">Next</a></li>
                  <?php endif; ?>
                </ul>
                <?php endif; ?>
                
                
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!--############################################### Admin End ######################################-->
<?php
// }
}else{
 echo "<script>
window.location.href = 'unauthorized.php';
</script>";
exit();
    
}
?>

<?php include('footer.php'); ?>
<script>
    $('#gameID').change(function(){
        var gameID = $('#gameID').val();
        
        if(gameID != ''){
            $.ajax({    //create an ajax request to 
                type: "POST",
                url: "calculate-bid-amount-ajax.php",             
                data:{gameID:gameID},  //expect html to be returned                
                success: function(data){
                    $('#bidAmount').text(data);
                }
            });
        }else{
            alert('Please Select Game!');
        }
    });
    
    
// game Details Single Ank
    $('#getDetails').click(function(){
        var game_id = $('#game_id').val();
        var session = $('#session').val();
        
        if(game_id != '' && session != ''){
            $.ajax({    //create an ajax request to 
                type: "POST",
                url: "calculate-single-ank-bid.php",             
                data:{gameID:game_id, session:session},  //expect html to be returned                
                success: function(data){
                    $('#singleAnks').html(data);
                }
            });
        }else{
            alert('Please Select Game & Session!');
        }
        
    });
    
    var game_id = $('#game_id').val();
        var session = $('#session').val();
        
        if(game_id != '' && session != ''){
            $.ajax({    //create an ajax request to 
                type: "POST",
                url: "calculate-single-ank-bid.php",             
                data:{gameID:game_id, session:session},  //expect html to be returned                
                success: function(data){
                    $('#singleAnks').html(data);
                }
            });
        }else{
            alert('Please Select Game & Session!');
        }


        // show declare result
    $('#go').click(function(){
        var date = $('#resultDate').val();
        var refcodeq = $('#refCodeq').val();
        var idd = $('#iDd').val();
        var gameId = $('#gameId').val();
        var session = $('#session').val();
        var type = $('#type').val();
        
        if((date) && (gameId) && (session)){
            $.ajax({    //create an ajax request to 
                type: "POST",
                url: "sell-report-ajax.php",             
                data:{resultDate:date, gameID:gameId, session:session, type:type, refCodeq:refcodeq, iDd:idd},   //expect html to be returned                
                success: function(data){
                    console.log(data);
                    $('#result_data').html(data);
                }
            });
        }
        
});
</script>
