<?php include('header.php'); 
?>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Risk Report</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
<!--               <li class="breadcrumb-item active">Sell Report</li>
 -->            </ol>
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
                        
                    <div class="row">
                        <input type="hidden" name="csrf_token"  id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" id="resultDate" value="<?php echo date('Y-m-d'); ?>" class="form-control" />
                                <input type="hidden" name="refcodeq" id="refCodeq" value="<?php echo htmlspecialchars($refcodeq) ?>" class="form-control" />
                            <input type="hidden" name="iDd" id="iDd" value="<?php echo htmlspecialchars($idd); ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                            <label>Game</label>
                            <select id="gameId" name="market" class="form-control select2bs4" style="width: 100%;">
                                <option value="" selected disabled>Select Game</option>
                                <?php
                                    $game = mysqli_query($con,  "SELECT * FROM `gametime_new` ORDER BY market DESC");
                                    $i = 1;
                                    $currentDate = date('Y-m-d');
                                    while($row = mysqli_fetch_array($game)){
                                       
                                    $xc = getOpenCloseTiming($row);
                                       
                                ?>
                                    <option value="<?php echo htmlspecialchars($row['market']); ?>"><?php echo htmlspecialchars($row['market']); ?> (<?php echo htmlspecialchars($xc['open']).' - '.htmlspecialchars($xc['close']); ?>)</option>
                                <?php
                                                        
                                        
                                    $i++;
                                    }
                                ?>
                                <?php
                                    // $game = mysqli_query($con,  "SELECT * FROM `gametime_manual` ORDER BY market DESC");
                                    $game = mysqli_query($con, "SELECT * FROM `gametime_manual`  ORDER BY `open` ASC");

                                    $i = 1;
                                    $currentDate = date('Y-m-d');
                                    while($row = mysqli_fetch_array($game)){
                                       
                                       
                                    $xc = getOpenCloseTiming($row);
                                ?>
                                    <option value="<?php echo htmlspecialchars($row['market']); ?>"><?php echo htmlspecialchars($row['market']); ?> (<?php echo htmlspecialchars($xc['open']).' - '.htmlspecialchars($xc['close']); ?>)</option>
                                <?php
                                                        
                                        
                                    $i++;
                                    }
                                ?>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Session</label>
                                <select id="session" name="session" class="form-control" style="width: 100%;">
                                    <option value="" selected disabled>Select Session</option>
                                    <option value="open">Open</option>
                                    <option value="close">Close</option>
                                </select>
                            </div>
                        </div>
                        
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
                     
                     
                        <div class="col-md-2">
                            <div class="form-group mt-2">
                              
                                  <button type="button" id="go"class="btn btn-primary mt-4">Submit</button>
                                  
                              </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->
                </div>
            
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
              <div class="card-body" id="result_data">
               
            
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
// show declare result
    $('#go').click(function(){
        var date = $('#resultDate').val();
        var refcodeq = $('#refCodeq').val();
        var idd = $('#iDd').val();
        var gameId = $('#gameId').val();
        var session = $('#session').val();
        // var type = $('#type').val();
        var csrf_token = $('#csrf_token').val();

        if((date) && (gameId) && (session) && (csrf_token)){
            $.ajax({    //create an ajax request to 
                type: "POST",
                url: "risk-report-ajax.php",             
                data:{resultDate:date, gameID:gameId, session:session,  refCodeq:refcodeq, iDd:idd, csrf_token:csrf_token},   //expect html to be returned                
                success: function(data){
                    console.log(data);
                    $('#result_data').html(data);
                }
            });
        }
        
});

</script>