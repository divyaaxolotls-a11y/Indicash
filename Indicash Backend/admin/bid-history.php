<?php include('header.php');
if (in_array(12, $HiddenProducts)){

$date = date('d/m/Y');

if (isset($_GET['query'])) {
    $search = htmlspecialchars($_GET['query'], ENT_QUOTES, 'UTF-8');
    $search = "user LIKE '%$search%' OR game LIKE '%$search%' OR bazar LIKE '%$search%' OR number LIKE '%$search%' OR amount LIKE '%$search%'";
    $result = mysqli_query($con, "SELECT * FROM games WHERE ($search) AND date='$date' ORDER BY sn DESC");
} else {
    $result = mysqli_query($con, "SELECT * FROM games WHERE date='$date' ORDER BY sn DESC");
}
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Bid History</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Bid History</li>
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
                    <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" id="date" value="<?php echo date('Y-m-d'); ?>" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Game Name</label>
                            <select id="game_id" class="form-control select2bs4" style="width: 100%;">
                                <option value="" selected disabled>Select Game</option>
                                <?php
                                $gameList = mysqli_query($con, "SELECT * FROM `gametime_new` WHERE `active`='1' ORDER BY sn DESC");
                                while ($row = mysqli_fetch_array($gameList)) {
                                    echo '<option value="' . htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8') . '</option>';
                                }
                                ?>
                                <?php
                                $gameList = mysqli_query($con, "SELECT * FROM `gametime_manual` WHERE `active`='1' ORDER BY sn DESC");
                                while ($row = mysqli_fetch_array($gameList)) {
                                    echo '<option value="' . htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8') . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Game Type</label>
                            <select class="form-control select2bs4" id="game_type" style="width: 100%;">
                                <option value="" selected disabled>Select Game Type</option>
                                <option value="single">Single Digit</option>
                                <option value="jodi">Jodi Digit</option>
                                <option value="singlepatti">Single Pana</option>
                                <option value="doublepatti">Double Pana</option>
                                <option value="triplepatti">Triple Pana</option>
                                <option value="halfsangam">Half Sangam</option>
                                <option value="fullsangam">Full Sangam</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mt-2">
                            <button id="fetchData" class="btn btn-primary mt-4">SAVE</button>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
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
                            <button class="btn btn-primary">Bid History</button>
                        </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive" id="full_main">
                        <table id="example1" class="w-100 table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User Name</th>
                                    <th>Mobile Number</th>
                                    <th>Bid TXID</th>
                                    <th>Game Name</th>
                                    <th>Game Type</th>
                                    <th>Number</th>
                                    <th>Points</th>
                                    <th>Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tbody">
                                <?php
                                $i = 1;
                                while ($row = mysqli_fetch_array($result)) {
                                    $userID = $row['user'];
                                    if($idd != 'admin@gmail.com'){
                                        $user = mysqli_query($con, "SELECT name,mobile FROM `users` WHERE `mobile`='$userID' AND refcode = '$refcodeq'");
                                    } else {
                                        $user = mysqli_query($con, "SELECT name,mobile FROM `users` WHERE `mobile`='$userID'");
                                    }
                                    $fetch = mysqli_fetch_array($user);
                                    $game_id = $row['bazar'];
                                    if ($fetch['mobile'] != '') {
                                    ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo htmlspecialchars($fetch['name'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($fetch['mobile'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['sn'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($game_id, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="text-transform: capitalize;"><?php echo htmlspecialchars($row['game_type'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['number'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['amount'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['timestamp'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><a class="btn btn-info" href="update-bid-history.php?id=<?php echo $row['sn']; ?>">Edit</a></td>
                                    </tr>
                                    <?php
                                    $i++;
                                    }
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

<?php 
    
}else{ 
echo "<script>
window.location.href = 'unauthorized.php';
</script>";
exit();
}
include('footer.php'); ?>

<script>
    $('#fetchData').click(function () {
        var date = $('#date').val();
        var gameID = $('#game_id').val();
        var gameType = $('#game_type').val();
        var csrf_token = $('#csrf_token').val();

        $.ajax({
            type: "POST",
            url: "bid-history-ajax.php",
            data: {
                date: date,
                gameID: gameID,
                gameType: gameType,
                csrf_token: csrf_token
            },
            success: function (response) {
                $("#full_main").html(response);
            }
        });
    });

    $(function () {
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });
    });
</script>
