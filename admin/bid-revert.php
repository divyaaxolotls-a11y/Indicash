<?php include('header.php');

?>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Bid Revert</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Bid Revert</li>
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
                <div class="card-body">
                    <form method="get">
                        
                        <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" id="date" value="<?php if(isset($_GET['date'])) { echo $_GET['date']; } else { echo date('Y-m-d'); } ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                            <label>Game</label>
                            <select id="game_id" name="gameID" class="form-control select2bs4" style="width: 100%;">
                                <option value="" selected disabled>Select Game</option>
                                <?php 
                                $gameList = mysqli_query($con, "SELECT * FROM `gametime_new` WHERE `active`='1' ORDER BY sn DESC");
                                while($row = mysqli_fetch_array($gameList)){
                                    ?>
                                    <option value="<?php echo $row['market']; ?>"><?php echo $row['market']; ?></option>
                                    <?php
                                        }
                                    ?>
                                     <?php 
                                        $gameList = mysqli_query($con, "SELECT * FROM `gametime_manual` WHERE `active`='1' ORDER BY sn DESC");
                                        while($row = mysqli_fetch_array($gameList)){
                                    ?>
                                    <option value="<?php echo $row['market']; ?>"   <?php if(isset($_GET['gameID']) && $_GET['gameID'] == $row['market']) { echo "selected"; } ?>><?php echo $row['market']; ?></option>
                                    <?php
                                        }
                                    ?>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mt-2">
                                <button class="btn btn-primary mt-4" type="submit" id="go">SAVE</button>
                            </div>
                        </div>
                    </div>
                        
                    </form>
                    <!-- /.row -->
                </div>
            </div>
            <!-- /.card -->

            <!-- After Save results show buttons Show Winners & Declare Results -->
            <div class="card card-default">
                <!-- /.card-header -->
                <div class="card-body center">
                    <button type="button" id="clearRefund" class="btn btn-success">Clear & Refund All</button>
                </div>
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
                    <button class="btn btn-primary">History</button>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body" id="main-table">
                  <?php  if(isset($_GET['gameID'])){ ?>
               <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>User Mobile</th>
                    <th>Bid Points</th>
                    <th>Bid Number</th>
                  </tr>
                  </thead>
                  <tbody id="tbody">
                        
                        <?php 
                        
                        $search_url_add = "&date=".$_GET['date']."&gameID=".$_GET['gameID'];
                        
                             $date = date('d/m/Y',strtotime($_GET['date']));
                             
                            
                             
                            $gameID = $_GET['gameID'];
                            
                             
                            $market = str_replace(" ","_",$gameID);
                            $market_1 = str_replace(" ","_",$gameID.' OPEN');
                            $market_2 = str_replace(" ","_",$gameID.' CLOSE');
                            
                            
                        $num_results_on_page = 10;  
                        if (isset($_GET["page"])) {
                        	$page  = $_GET["page"]; 
                        	} 
                        	else{ 
                        	$page=1;
                        	};  
                        $start_from = ($page-1) * $num_results_on_page;  
                        
                        
                        
                        $result = mysqli_query($con,"SELECT * FROM `games` WHERE `date`='$date' AND (`bazar`='$market' OR `bazar`='$market_1' OR `bazar`='$market_2' )  LIMIT $start_from, $num_results_on_page");
                            
                        $result_db = mysqli_query($con,"SELECT COUNT(sn) FROM `games` WHERE `date`='$date' AND (`bazar`='$market' OR `bazar`='$market_1' OR `bazar`='$market_2' ) "); 
                        
                        $action_url = "&page=".$page.$search_url_add;
                        
                        
                        $row_db = mysqli_fetch_row($result_db);  
                        $total_pages = $row_db[0];  
                        
                        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
                        
                        
                             
                            $i = 1;
                            
                            while($row = mysqli_fetch_array($result)){
                                $userID = $row['user'];
                                $users = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$userID' ");
                                $userFetch = mysqli_fetch_array($users);
                                
                        
                        ?>
                        
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $userFetch['name']; ?></td>
                            <td><?php echo $userFetch['mobile']; ?></td>
                            <td><?php echo $row['amount']; ?></td>
                            <td><?php echo $row['number']; ?></td>
                        </tr>
                        
                        <?php
                        $i++;
                        }
                        ?>
                        
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
                        
                        <?php } ?>
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
// $('#go').click(function(){
//     var date = $('#date').val();
//     var gameID = $('#game_id').val();
    
//     if(date != '' && gameID != ''){
//         $.ajax({    //create an ajax request to display.php
//             type: "POST",
//             url: "bid-revert-ajax.php",             
//             data:{date:date, gameID:gameID},   //expect html to be returned                
//             success: function(response){ 
//                 $('.center').show();
//                 $("#main-table").html(response); 
//             }
//         });
//     }
// });

$('#clearRefund').click(function() {
    var date = $('#date').val();
    var gameID = $('#game_id').val();
    
    if (date !== '' && gameID !== '') {
        $.ajax({
            type: "POST",
            url: "bid-revert-refund.php",
            data: {date: date, gameID: gameID},
            success: function(response) {
                console.log('Server response: ' + response);
                
                // Handle different response values
                if (response.trim() === '1') {
                    location.reload();
                } else if (response.trim() === '0') {
                    alert('Error: Operation failed!');
                } else {
                    alert('Unexpected response from server: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
                alert('An error occurred while processing your request.');
            }
        });
    } else {
        alert('Please provide both date and game ID.');
    }
});



    $(function () {
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    });
</script>