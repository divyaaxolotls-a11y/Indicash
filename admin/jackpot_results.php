<?php 
error_reporting(0);
ini_set('display_errors', 0);
include('header.php');
include('config.php');
// echo date("Y-m-d H:i:s");
if (isset($_REQUEST['submit']))
{
    extract($_REQUEST);
    
    
    $date = date('d/m/Y',strtotime($_POST['date']));
    $game_id = explode("_",$gameId);
    $market = $game_id[1];
    $timing = $game_id[2];
  
  if(mysqli_num_rows(mysqli_query($con,"select sn from jackpot_results where market='$market' AND date='$date' AND timing = '$timing'")) > 0){

    	echo "<script>window.location.href = 'starline-declare-result.php?error=Result%20already%20published'</script>";
    return;
    }
    
   if(strlen($open) != 1){

    $error = "Digit must be 1 digit";

}else{

$batch_id = md5($stamp.$market.rand().$open.$date.$timing.$stamp);

/* INSERT RESULT */
mysqli_query($con,
"INSERT INTO jackpot_results
(market,timing,number,date,created_at)
VALUES
('$market','$timing','$open','$date','$stamp')"
);


/* SAVE BATCH */
$batch_result = $open;
$b_market = $market.'~'.$timing;

mysqli_query($con,
"INSERT INTO manual_batch
(market,result,revert,created_at,batch_id,date)
VALUES
('$b_market','$batch_result','0','$stamp','$batch_id','$date')"
);


/* GET timing_sn FROM jackpot_markets */

$timing_q = mysqli_query($con,
"SELECT sn FROM jackpot_markets
WHERE name='$market'
LIMIT 1");

if(mysqli_num_rows($timing_q)==0){
die("Market not found");
}

$timing_row = mysqli_fetch_array($timing_q);
$timing_sn = $timing_row['sn'];


/* GET WINNING USERS */

$get_games = mysqli_query($con,
"SELECT * FROM jackpot_games
WHERE bazar='$market'
AND timing_sn='$timing_sn'
AND date='$date'
AND status='0'
AND number='$open'"
);


while($x = mysqli_fetch_array($get_games)){

$sn = $x['sn'];
$user = $x['user'];

/* SIMPLE WIN AMOUNT */
$amount = $x['amount'];

$remrk = "Jackpot for market | ".$market." Winning Number |".$open;

mysqli_query($con,
"UPDATE jackpot_games
SET status='1',
win_amount='$amount'
WHERE sn='$sn'"
);

mysqli_query($con,
"UPDATE users
SET wallet=wallet+'$amount'
WHERE mobile='$user'"
);

mysqli_query($con,
"INSERT INTO transactions
(user,amount,type,remark,created_at,batch_id,game_id)
VALUES
('$user','$amount','1','$remrk','$stamp','$batch_id','$sn')"
);
            sendNotification("Congratulations","You won $amount",$user);

}


/* RESULT FORMAT */

$result = $open;

$body = $market.' '.$timing.' Jackpot Result';

sendNotification($body,$result,"result");

} 
    if(isset($_REQUEST['redirect_back'])){
        header("location:starline-declare-result.php");
    }
}


?>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Jackpot Declare Result</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Jackpot Declare Result</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
          
               <?php if(isset($_REQUEST['error'])) { ?>
          <div class="alert alert-danger" role="alert">
          <?php echo $_REQUEST['error']; ?>
        </div>
          <?php } ?>
          
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
                    <form method="post">
                        
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" id="resultDate" value="<?php echo date('Y-m-d'); ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                            <label>Game</label>
                            <select id="gameId" name="gameId" class="form-control select2bs4" style="width: 100%;">
                                <option value="" selected disabled>Select Game</option>
                                 <?php 
                                $gameList = mysqli_query($con, "SELECT * FROM `starline_markets` WHERE `active`='1' ORDER BY sn DESC");
                                while($rowx = mysqli_fetch_array($gameList)){
                                    
                                    $name = $rowx['name'];
                                    
                                    $gameListx = mysqli_query($con, "SELECT * FROM `starline_timings` WHERE market='$name'");
                                while($row = mysqli_fetch_array($gameListx)){
                                    
                                    $name = $row['name'];
                                ?>
                                <option value="starline_<?php echo $row['market']; ?>_<?php echo $row['close']; ?>"><?php echo $row['market'].' '.$row['close']; ?></option>
                                <?php
                                    } }
                                ?>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                            <label>Pana</label>
                            <select name="panna" class="form-control select2bs4" id="pana" style="width: 100%;">
                                <option value="" selected disabled>Select Pana</option>
                                <?php
                                    $pana = mysqli_query($con, "SELECT * FROM `full_sangam` ORDER BY close_ank ASC ");
                                    while($row = mysqli_fetch_array($pana)){
                                ?>
                                    <option value="<?php echo $row['close_ank']; ?>"><?php echo $row['close_ank']; ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Digit</label>
                                <input  name="open" type="number" id="digit" class="form-control" value=""  readonly />
                            </div>
                        </div>
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mt-2">
                                  <button type="button" onclick="$(this).hide(); $('#show_buttons').show();" class="btn btn-primary mt-4">SAVE</button>
                                <div id="show_buttons" style="display:none;">
                                  
                                  <button name="submit" type="submit" class="btn btn-primary mt-4">DECLARE RESULT</button>
                              
                                <button id="showResult" type="button" class="btn btn-primary mt-4">SHOW WINNERS</button>
                                  
                              </div>
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
                <div class="card-body center" style='display:none'>
                    <button type="button" id="showResult" class="btn btn-success">Show Winners</button>
                    <button type="button" id="declareResult" class="btn btn-info">Declare Result</button>
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
                    <input type="date" id="PrevResultFilterDate" value="<?php echo date('Y-m-d'); ?>" class="form-control" required/>
                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>Game Name</th>
                    <th>Result Date</th>
                    <th>Open Pana</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody id="tbody">
                    <?php
                        $currentDate = date('d/m/Y');
                        $result = mysqli_query($con, "SELECT * FROM `jackpot_results` WHERE `date`='$currentDate' ORDER BY sn DESC");
                        $i = 1;
                        while($row = mysqli_fetch_array($result)){
                            
                    ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $row['market'].' '.$row['timing']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><span class="badge badge-danger"><?php echo $row['panna'].' - '.$row['number']; ?></span></td>
                            <td>
                            <button class="btn btn-danger" data-toggle="modal" data-target="#deleteResult<?php echo $i; ?>">Delete</button>
                            </td>
                        </tr>
                        <!--Delete Result-->
                        <div class="modal fade" id="deleteResult<?php echo $i; ?>">
                                                <div class="modal-dialog modal-dialog-centered">
                                                  <div class="modal-content">
                                                  
                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                      <h4 class="modal-title">Are you sure you want to delete this result?</h4>
                                                    </div>
                                                    
                                                    <!-- Modal body -->
                                                    <div class="modal-body">
                                                      <form method="post">
                                                          <input type="hidden" name="resultDate" value="<?php echo $row['date']; ?>" required/>
                                                          <input type="hidden" name="gameID" value="<?php echo $row['market'].'~'.$row['timing']; ?>" required/>
                                                          <!-- Modal footer -->
                                                            <div class="modal-footer">
                                                              <button type="button" class="btn btn-info" data-dismiss="modal">No</button>
                                                              <button type="submit" name="deleteResult" class="btn btn-success">Yes</button>
                                                            </div>
                                                      </form>
                                                    </div>
                                                    
                                                    
                                                    
                                                  </div>
                                                </div>
                                              </div>
                    <?php
                        $i++;
                        }
                                                
                        if(isset($_POST['deleteResult'])){
                                                 $resultDate = $_POST['resultDate'];
                                $gameID = $_POST['gameID'];
                                                    
                          
                          		$exGam = explode("~",$gameID);
                                $mrk = $exGam[0];
                                $timing = $exGam[1];
                          
                                //delete result
                                $deleteResult = mysqli_query($con,  "DELETE FROM `jackpot_results` WHERE `market`='$mrk' AND `date`='$resultDate' AND timing='$timing'");
                                
                                
                               mysqli_query($con," update starline_games set status='0', is_loss='0' where date='$resultDate' AND bazar='$mrk' AND timing_sn='$timing'");
                                
                                                    
                                $winHistory = mysqli_query($con,  "SELECT * FROM `manual_batch` WHERE `market`='$gameID' AND `date`='$resultDate' AND revert='0'");
                                while($winFetch = mysqli_fetch_array($winHistory)){
                                    $bidTX = $winFetch['batch_id'];
                                    $sn = $winFetch['sn'];
                                    
                                    mysqli_query($con,"update manual_batch set revert='1' where sn='$sn'");
                                    
                                    $result = mysqli_query($con,  "SELECT * FROM `transactions` WHERE `batch_id`='$bidTX'");
                                    while($row = mysqli_fetch_array($result)){
                                        
                                        $game_id = $row['game_id'];
                                        $user = $row['user'];
                                        $amount = $row['amount'];
                                        
                                        
                                       mysqli_query($con, "update users set wallet=wallet-'$amount' where mobile='$user'");
                                    }
                                    
                                    mysqli_query($con, "delete from transactions where batch_id='$bidTX'");               
                                }
                          
                                echo "<script>window.location.href='starline-declare-result.php';</script>";
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
    
<!--Show Winners Model-->
  <div class="modal fade" id="showWinners">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Winners List</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body" style="overflow: scroll;">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User Name</th>
                        <th>Bid Points</th>
                        <th>Winning Points</th>
                        <th>Market Name</th>
                        <th>Game Name</th>
                        <th>Bid number</th>
                        <th>Date</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody id="showWinnersTable">
                    
                </tbody>
            </table>
        </div>
        
        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
        
      </div>
    </div>
  </div>

<?php include('footer.php'); ?>
<script>
// save declare result
    $('#go').click(function(){
        var date = $('#resultDate').val();
        var gameId = $('#gameId').val();
        var pana = $('#pana').val();
        var digit = $('#digit').val();
        
        if((date) && (gameId) && (pana) && (digit)){
            $.ajax({    //create an ajax request to 
                type: "POST",
                url: "starline-declare-result-ajax.php",             
                data:{resultDate:date, gameID:gameId, pana:pana, digit:digit},   //expect html to be returned                
                success: function(data){
                    var obj = JSON.parse(data);
                    if(obj.result== false){
                        alert(obj.msg);
                    }else{
                        $('.center').css('display','block');
                    }
                    
                }
            });
        }
        
});

// Show Winner list
$('#showResult').click(function(){
        var date = $('#resultDate').val();
        var gameId = $('#gameId').val();
        var pana = $('#pana').val();
        var digit = $('#digit').val();
    if((date) && (gameId)){
        $.ajax({    //create an ajax request to 
                type: "POST",
                url: "starline-show-winners-ajax.php",             
                data:{date:date, gameID:gameId,panna:pana,digit:digit},   //expect html to be returned                
                success: function(data){
                    $('#showWinnersTable').html(data);
                    $('#showWinners').modal('show');
                }
            });
    }    
});

// Declare Result 
$('#declareResult').click(function(){
    var date = $('#resultDate').val();
    var gameId = $('#gameId').val();
    
    if((date) && (gameId)){
        $.ajax({    //create an ajax request to 
                type: "POST",
                url: "starline-declare-ajax.php",             
                data:{resultDate:date, gameID:gameId},   //expect html to be returned                
                success: function(data){
                    location.reload();
                    // $('#showWinnersTable').html(data);
                    // $('#showWinners').modal('show');
                }
            });
    }  
})


$(function () {
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    })
});

$('#pana').change(function(){
        var pana = $(this).val();
        var dsum = 0;
        for (var i = 0; i < pana.length; i++)
          {
        
            if (/[0-9]/.test(pana[i])) dsum += parseInt(pana[i])
          }
          var dd = dsum.toString();
          var res = dd.charAt(dd.length-1);
          
          $('#digit').val(res);
});


//PrevResultFilterDate
$('#PrevResultFilterDate').change(function(){
    var PrevResultdate = $('#PrevResultFilterDate').val();
    
    if(PrevResultdate != ''){
        $.ajax({    //create an ajax request to 
            type: "POST",
            url: "filter-starline-result-ajax.php",             
            data:{resultDate:PrevResultdate},  //expect html to be returned                
            success: function(data){
                $('#tbody').html(data);
            }
        });
    }
});
</script>