<?php include('header.php');
if (in_array(1, $HiddenProducts)){ ?>
    
<div class='p-4'>
<form action="" method="post">

  <div class="form-group row">
    <label  class="col-sm-2 col-form-label">Role</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputPassword3" placeholder="Role" name="role" required>
    </div>
  </div>

  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-sm-2 pt-0">Select Permissions</legend>
      <div class="col-sm-10">
        <div class="form-check">
          <input class="form-check-input" type="checkbox"  id="gridRadios1" value="1" name="task[]">
          <label class="form-check-label" for="gridRadios1">
            Dashboard
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="2" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Task Management
          </label>
        </div>

        <!--<div class="form-check">-->
        <!--  <input class="form-check-input" type="checkbox" id="gridRadios2" value="3" name="task[]">-->
        <!--  <label class="form-check-label" for="gridRadios2">-->
        <!--    User Managment-->
        <!--  </label>-->
        <!--</div>-->

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="4" name="task[]">
          <label class="form-check-label" for="gridRadios2">
            User Managment 
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="5" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Winner prediction
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="6" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Profit/Loss
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="7" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Declare Result
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="8" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Winning Details
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="9" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Image Slider
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="10" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Bet Filter
          </label>
        </div>
        
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="11" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Customer Sell Report
          </label>
        </div>
        
         <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="21" name="task[]">
          <label class="form-check-label" for="gridRadios2">
            Load Report
          </label>
        </div>
        
         <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="22" name="task[]">
          <label class="form-check-label" for="gridRadios2">
            Risk Report
          </label>
        </div>
        
     


        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="12" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Report Management
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="13" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Wallet Management
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="14" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Settings
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="15" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Game Managment
          </label>
        </div>

        <div class="form-check" >
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="16" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Notice Managment
          </label>
        </div>

        <div class="form-check" style="display:none;">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="17" name="task[]">
          <label class="form-check-label" for="gridRadios2">
          Games & Numbers
          </label>
        </div>

        <!--<div class="form-check">-->
        <!--  <input class="form-check-input" type="checkbox" id="gridRadios2" value="18" name="task[]">-->
        <!--  <label class="form-check-label" for="gridRadios2">-->
        <!--  Personal Games-->
        <!--  </label>-->
        <!--</div>-->

        <!--<div class="form-check" style="display:none;">-->
        <!--  <input class="form-check-input" type="checkbox" id="gridRadios2" value="19" name="task[]">-->
        <!--  <label class="form-check-label" for="gridRadios2">-->
        <!--  Delhi Jodi-->
        <!--  </label>-->
        <!--</div>-->
        
             <div class="form-check">
          <input class="form-check-input" type="checkbox" id="gridRadios2" value="20" name="task[]">
          <label class="form-check-label" for="gridRadios2">
            Add Reviews
          </label>
        </div>
        
           

      </div>
    </div>
  </fieldset>
  
  


  <div class="form-group row">
    <label  class="col-sm-2 col-form-label"></label>
    <div class="col-sm-10">
      <input type="submit" class="btn btn-primary btn-lg" value="Submit" name="save">
    </div>
  </div>
</form>
</div>

<?php
if (isset($_POST['save'])) {

      $task = implode(',', $_POST['task']);
      echo $task;
      $role =  $_POST['role'];
      $sql= "INSERT INTO `task_manager`(`role`,`tasks`) VALUES ('". $role ."','".$task."')";
      $query=mysqli_query($con,$sql);
      
      $remark = 'New Role is Created. '.$role;
      log_action($remark); 
      
      if($query){
        echo "<script>
        alert('Role Created');
        window.location.href='task.php';
        </script>";
      }
  }

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
</script>
