<?php include('header.php'); 
       if (in_array(5, $HiddenProducts)){ 
?>

<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Winning prediction</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Winning prediction</li>
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
                    <form method="post">
                    <input type="hidden" name="csrf_token"  id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
                            <select id="gameId" name="market" class="form-control select2bs4" style="width: 100%;">
                                <option value="" selected disabled>Select Game</option>
                              
                                <?php
                                    $game = mysqli_query($con,  "SELECT * FROM `gametime_manual` ORDER BY market DESC");
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
                        <div class="col-md-2">
                            <div class="form-group">
                            <label>Open Pana</label>
                            <select id="pana" name="panna" class="form-control select2bs4" style="width: 100%;">
                                <option value="" selected disabled>Select Pana</option>
                                <?php
                                    $panna_numbers = getPatti();
                                    for($x = 0; $x < count($panna_numbers); $x++){
                                ?>
                                <option value="<?php echo htmlspecialchars($panna_numbers[$x]); ?>"><?php echo htmlspecialchars($panna_numbers[$x]); ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            </div>
                        </div>
                        <input name="digit" type="hidden" id="digit" class="form-control" value=""  readonly />
                      
                       <div class="col-md-2">
                            <div class="form-group">
                            <label>Close Pana</label>
                            <select id="cpana" name="cpanna" class="form-control select2bs4" style="width: 100%;">
                                <option value="" selected disabled>Select Pana</option>
                                <?php
                                    $panna_numbers = getPatti();
                                    for($x = 0; $x < count($panna_numbers); $x++){
                                ?>
                                <option value="<?php echo htmlspecialchars($panna_numbers[$x]); ?>"><?php echo htmlspecialchars($panna_numbers[$x]); ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            </div>
                        </div>
                        <input name="cdigit" type="hidden" id="cdigit" class="form-control" value=""  readonly />
                      
                      
                        <div class="col-md-2">
                            <div class="form-group mt-2">
                              
                                 
                                <button id="showResult" type="button" class="btn btn-primary mt-4">Submit</button>
                                  
                            </div>
                        </div>
                    </div>
                    </form>
                    <!-- /.row -->
                </div>
            </div>
            <!-- /.card -->

            <!-- After Save results show buttons Show Winners & Declare Results -->
            <div class="card card-default center" style='display:none'>
                <!-- /.card-header -->
                <div class="card-body">
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
            <div class="card" id='tbody'>
              
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
        <div class="modal-body" style="    overflow: scroll;">
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
  
<?php }else{ 

 echo "<script>
                        window.location.href = 'unauthorized.php';
                    </script>";
    exit();
}
 include('footer.php'); ?>
<script>
// show declare result
    $('#go').click(function(){
        var date = $('#resultDate').val();
        var gameId = $('#gameId').val();
        var session = $('#session').val();
        var pana = $('#pana').val();
        var digit = $('#digit').val();
        
        if((date) && (gameId) && (session) && (pana) && (digit)){
            $.ajax({    //create an ajax request to 
                type: "POST",
                url: "declare-result-ajax.php",             
                data:{resultDate:date, gameID:gameId, session:session, pana:pana, digit:digit},   //expect html to be returned                
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
        var session = $('#session').val();
        var pana = $('#pana').val();
        var digit = $('#digit').val();
        var cpana = $('#cpana').val();
        var cdigit = $('#cdigit').val();
        var csrf_token = $('#csrf_token').val();

        if((date) && (gameId) && (session)){
            $.ajax({    //create an ajax request to 
                    type: "POST",
                    url: "winner-predication-ajax.php",             
                    data:{date:date, market:gameId,session:session,panna:pana,digit:digit,cpanna:cpana,cdigit:cdigit,csrf_token:csrf_token},  //expect html to be returned                
                    success: function(data){
                        $('#tbody').html(data);
                       // $('#showWinners').modal('show');
                    }
                });
        }    
    });
  
  function editGame(sn){
    
        var number = $('#Betnumber'+sn).val();
    $('#EditGame'+sn).modal('hide')
    	  $.ajax({    //create an ajax request to 
                    type: "POST",
                    url: "winner-predication-ajax-update.php",             
                    data:{sn:sn, number:number},  //expect html to be returned                
                    success: function(data){
                        
                        var date = $('#resultDate').val();
                        var gameId = $('#gameId').val();
                        var session = $('#session').val();
                        var pana = $('#pana').val();
                        var digit = $('#digit').val();
                        var cpana = $('#cpana').val();
                        var cdigit = $('#cdigit').val();

                        if((date) && (gameId) && (session)){
                            $.ajax({    //create an ajax request to 
                                    type: "POST",
                                    url: "winner-predication-ajax.php",             
                                    data:{date:date, market:gameId,session:session,panna:pana,digit:digit,cpanna:cpana,cdigit:cdigit},  //expect html to be returned                
                                    success: function(data){
                                        $('#tbody').html(data);
                                       // $('#showWinners').modal('show');
                                    }
                                });
                        }    
                      
                    }
                });
    
  }
    
// Auto Calculate Digit
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
    
    $(function () {
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    });
  
  
// Auto Calculate Digit
$('#cpana').change(function(){
        var pana = $(this).val();
        var dsum = 0;
        for (var i = 0; i < pana.length; i++)
          {
        
            if (/[0-9]/.test(pana[i])) dsum += parseInt(pana[i])
          }
          var dd = dsum.toString();
          var res = dd.charAt(dd.length-1);
          
          $('#cdigit').val(res);
});
    
    
    
    
</script>