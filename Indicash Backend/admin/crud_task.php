<?php include('header.php'); if (in_array(2, $HiddenProducts)){ ?>
<?php
  if (isset($_POST['edit'])) {
    $edit_id = $_POST['e_id'];
    $email   = $_POST['email'];
    $task   = $_POST['task'];
?>

<div style="display: flex;justify-content: center;">
<div style="padding:3em;width:25em;margin:1em;box-shadow:0 0 10px gray;border-radius:10px;">
<form method="post" action=''>
<input type="hidden" name="email" value="<?php echo $email; ?>">
    <p class="mb-3 text-center">Email : <?php echo $email ?></p>
<select class="form-control" id="role" name="role" required>
  <option value="" disabled>Select Role</option>
  <?php 
    $sql_role="SELECT SQL_NO_CACHE * FROM `task_manager` ";
    $result_role=mysqli_query($con,$sql_role)or die(mysql_error());
    $num=mysqli_num_rows($result_role);
    while($row_role=mysqli_fetch_array($result_role))
    {
  ?>
  <option <?php if($task==$row_role['id']){echo "Selected";} ?> value="<?php echo $row_role['id']?>"><?php echo $row_role['role']?></option>
  <?php
    }	  
  ?> 
  </select>
      <input type="submit" class="btn btn-primary mt-3 w-100" value="Assign" name="assign">
      <a href="task.php" class="btn btn-success mt-3 w-100"> Create New Role</a>
</form>
</div>
</div>


<?php
  }
  if (isset($_POST['delete'])) {
     $del_id = $_POST['d_id'];
	 mysqli_query($con, "DELETE FROM admin WHERE sn=$del_id");
     echo "<script>alert('Row deleted successfully...');window.location.href='crud_task.php';</script>";
  }
?>


<?php
  if (isset($_POST['assign'])) {
    $email = $_POST['email'];
    $role = $_POST['role'];
    $sql = mysqli_query($con, "UPDATE admin SET tasks='$role'  WHERE email='$email'");
    if($sql){
        echo "<script>alert('Role Assigned...');window.location.href='crud_task.php';</script>";
    }
  }
?>
<div class="table-responsive p-4">
<table id="example1" class="table table-bordered table-striped">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Email</th>
      <!--<th scope="col">Refferal Id</th>-->
      <th scope="col">Role</th>
      <!--<th scope="col">Wallet</th>-->
      <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>
  <?php
      $sql = "SELECT * FROM admin";
      $result = $con->query($sql);
      $i=1;
      while ($row = mysqli_fetch_array($result)) { 
          $tasks_id = $row['tasks'];
          $query2 = "SELECT role FROM task_manager WHERE id = $tasks_id";
          $result2 = mysqli_query($con, $query2);
          $row2 = mysqli_fetch_assoc($result2);
          $task_name = $row2['role'];
    ?>
    <tr>
      <th scope="row"><?php echo $i++;; ?></th>
      <td><?php echo $row['email']; ?></td>
      <!--<td><?php echo $row['ref_id']; ?></td>-->
      <td><?php echo $task_name; ?></td>
      <!--<td><?php echo $row['wallet']; ?></td>-->
      <td>
      <form action="" method="post">
		<!--<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addPointModal" data-wallet="<?php echo $row['wallet']; ?>" data-userid="<?php echo $row['sn']; ?>">Add Points</button>-->
		<?php if($row['email']!='admin@gmail.com'){ ?>
		<a href="<?php echo $row['sn']; ?>" class="edit_btn" ><input type="hidden" name="e_id" value="<?php echo $row['sn']; ?>"><input type="hidden" name="task" value="<?php echo $row['tasks']; ?>"><input type="hidden" name="email" value="<?php echo $row['email']; ?>"><input type='submit' value="Change Role" class='btn-sm btn-primary text-center' name="edit"></a>
		<a href="<?php echo $row['sn']; ?>" class="del_btn" onclick="return confirm('Are you sure want to delete?');"><input type="hidden" name="d_id" value="<?php echo $row['sn']; ?>"><input type='submit' value="Delete" class='btn-sm btn-danger' name="delete"></a>
		<?php } ?>
      </form>
	  </td>
    </tr>
    <?php } ?>
  </tbody>
</table>

<div class="modal fade" id="addPointModal" tabindex="-1" role="dialog" aria-labelledby="addPointModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPointModalLabel">Add Points</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="addPointsForm" method="post" autocomplete="off" action="addpoint.php">
          <input type="hidden" name="user_id" id="user_id_input" value="">
          <input type="hidden" name="wallet" id="walletBalance" value="">
          <div class="form-group">
            <label for="pointsAdd">Add Points</label>
            <input type="number" name="pointsAdd" id="pointsAdd" class="form-control" placeholder="Enter Points Here" required>
          </div>
          <div class="modal-footer">
            <input type="submit"  name="addponits" Value="Submit" class="btn btn-success">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</div>

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
<script>
  $('#addPointModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var userId = button.data('userid'); 
    var walletBalance = button.data('wallet');

    $('#user_id_input').val(userId);
    $('#walletBalance').val(walletBalance);
  });
</script>
<?php }else{ 

 echo "<script>
                        window.location.href = 'unauthorized.php';
                    </script>";
    exit();
    ?>
<?php include('footer.php');} ?>