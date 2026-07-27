<?php include('header.php');?>
<?php 
  $HiddenProducts = explode(',',$row2['tasks']);
  if (in_array(2, $HiddenProducts)){ ?>

<div class='p-4'>

<?php
if (isset($_POST['save'])) {

      $r_id   =  $_POST['r_id'];
      $email  =  $_POST['email'];
      $role   =  $_POST['role'];
      $pass   =  $_POST['pass'];
      $cpass  =  $_POST['cpass'];
      if($pass==$cpass){
                      $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

        $sql= "INSERT INTO `admin`(`email`,`tasks`,`ref_id`,`password`) VALUES ('". $email ."','". $role ."','".$r_id."','".$hashedPassword."')";
          $query=mysqli_query($con,$sql);
          
          if (!$query) {
              die('Error: ' . mysqli_error($con));
          }
          else{
                 $remark = 'task assigned successfully';
                log_action($remark); 

            echo "<div class='p-3 mb-4 bg-success text-center'>Role Created Sucessfully.</div>";
          }
      }
      else{
        echo "<div class='p-3 mb-4 bg-danger text-center'>  Password Not Matched </div>";
      }
  }
?>

<form action="" method="post">
  <div class="form-group row">
    <label  class="col-sm-2 col-form-label">Email</label>
    <div class="col-sm-10">
      <input type="email" class="form-control" id="inputPassword3" placeholder="Email" name="email"  required>
    </div>
  </div>
  <div class="form-group row" style="display:none;">
    <label  class="col-sm-2 col-form-label">Refferal Id</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputPassword3" placeholder="Refferal Id" name="r_id" value="<?php echo rand(1111,9999) ?>" required readonly>
    </div>
  </div>
  <!--<div class="form-group row">-->
  <!--  <label  class="col-sm-2 col-form-label">Commision </label>-->
  <!--  <div class="col-sm-10">-->
  <!--    <input type="text" class="form-control" id="inputPassword3" placeholder="Commision ( in % )" name="comisn" required >-->
  <!--  </div>-->
  <!--</div>-->
  <div class="form-group row">
  <label  class="col-sm-2 col-form-label">Role</label>
  <div class="col-sm-10">
  <select class="form-control" id="role" name="role" required>
  <option value="" selected disabled>Select Role</option>
  <?php 
    $sql_role="SELECT SQL_NO_CACHE * FROM `task_manager` where role !='Super Admin'";
    $result_role=mysqli_query($con,$sql_role)or die(mysql_error());
    $num=mysqli_num_rows($result_role);
    while($row_role=mysqli_fetch_array($result_role))
    {
  ?>
  <option value="<?php echo $row_role['id']?>"><?php echo $row_role['role']?></option>
  <?php
    }	  
  ?> 
  </select>
</div>
</div>
<div class="form-group row">
    <label  class="col-sm-2 col-form-label">Password</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputPassword3" placeholder="Password" name="pass" required>
    </div>
  </div>
  <div class="form-group row">
    <label  class="col-sm-2 col-form-label">Re-Enter Pasword</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="inputPassword3" placeholder="Re-Enter Pasword" name="cpass" required>
    </div>
  </div>
  <div class="form-group row">
    <label  class="col-sm-2 col-form-label"></label>
    <div class="col-sm-10">
      <input type="submit" class="btn btn-primary" value="Submit" name="save">
    </div>
  </div>
</form>
</div>


<?php }else{
 echo "<script>
                        window.location.href = 'unauthorized.php';
                    </script>";
    exit();
    
}
 include('footer.php'); ?>
