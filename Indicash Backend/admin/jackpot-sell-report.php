<?php include('header.php'); 


?>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Sell Report Jackpot</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Sell Report Jackpot</li>
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
                        
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" id="resultDate" value="<?php echo date('Y-m-d'); ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                            <label>Game</label>
                             <select id="gameId" name="gameId" class="form-control select2bs4" style="width: 100%;">
                                <option value="" selected disabled>Select Game</option>
                            
                                <?php 
                                $gameList = mysqli_query($con, "SELECT * FROM `jackpot_markets` WHERE `is_active`='1' ORDER BY sn ASC");
                            
                                while($row = mysqli_fetch_array($gameList)){
                                ?>
                                    <option value="jackpot_<?php echo $row['name']; ?>_<?php echo $row['close']; ?>">
                                        <?php echo $row['name'].' '.$row['close']; ?>
                                    </option>
                                <?php } ?>
                            
                            </select>
                            </div>
                        </div>
                      
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Game Type</label>
                                <select id="type" name="type" class="form-control" style="width: 100%;">
                                    <option value="" selected disabled>Select Session</option>
                                    <option value="Jodi">Jodi</option>
                                    <!--<option value="single">Single</option>-->
                                    <!--<option value="panna">Panna</option>-->
                                </select>
                            </div>
                        </div>
                     
                     
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
                    .colls p {
                        margin-bottom:0px;
    font-size: 19px;
    border: solid 1px #000;
    padding: 5px 11px
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
        var gameId = $('#gameId').val();
        var type = $('#type').val();
        
        if((date) && (gameId)){
            $.ajax({    //create an ajax request to 
                type: "POST",
                url: "sell-report-jackpot-ajax.php",             
                data:{resultDate:date, gameID:gameId, type:type},   //expect html to be returned                
                success: function(data){
                    console.log(data);
                    $('#result_data').html(data);
                }
            });
        }
        
});

</script>