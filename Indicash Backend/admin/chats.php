<?php include('header.php'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Chats</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Chats</li>
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
             
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>User Name</th>
                      <th>User Mobile</th>
                      <th>Unseen Msg</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    $user = mysqli_query($con, "SELECT * FROM `admin_chats` where user!='admin' group by user ORDER BY seen");
                    $i = 1;
                    while($row = mysqli_fetch_array($user)){
                      $mobile = $row['user'];
                      $user_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `users` where mobile='$mobile'"));
                      
                  ?>
                    <tr>
                      <td><?php echo $i; ?></td>
                      <td><?php echo $user_info['name']; ?></td>
                      <td>
                        <?php echo $user_info['mobile']; ?><br>
                        <a href="https://api.whatsapp.com/send?phone=91<?php echo $user_info['mobile']; ?>" target="_blank"><i class="fab fa-whatsapp" style="color:green;font-size:20px;"></i></a>
                        &nbsp;&nbsp;
                        <a href="tel:+91 <?php echo $user_info['mobile']; ?>" target="_blank"><i class="fas fa-phone-alt" style="font-size:20px;"></i></a>
                      </td>
                      <td><?php echo mysqli_num_rows(mysqli_query($con,"select sn from admin_chats where seen='0' AND user='$mobile'")) ?></td>
                      <td><a href="chat_screen.php?user=<?php echo $mobile; ?>"><button class="btn btn-info">Start Chat</button></a></td>
                      
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