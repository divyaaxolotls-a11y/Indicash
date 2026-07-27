<?php
session_start(); // Ensure the session is started

include('header.php');
if (in_array(20, $HiddenProducts)){

// CSRF Token check and handling for record deletion
if (isset($_GET['delete_id'])) {
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        // CSRF token is invalid, display error
        echo "<script>alert('Invalid CSRF token. Please refresh the page and try again.');</script>";
    } else {
        // Sanitize and delete record
        $delete_id = intval($_GET['delete_id']);  // Ensure the ID is an integer

        // Prepare delete query
        $delete_query = "DELETE FROM reviews_app WHERE id = ?";
        if ($stmt = $con->prepare($delete_query)) {
            $stmt->bind_param("i", $delete_id); // Bind the delete ID
            $stmt->execute();
            $stmt->close();
            
               $remark = 'Review is deleted '.$id;
              log_action($remark); 

            // Set session message and redirect
            $_SESSION['message'] = 'Record deleted successfully.';
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "' . $_SERVER['PHP_SELF'] . '";
                    }, 500); 
                  </script>';
            exit;
        } else {
            echo "<script>alert('Error deleting record.');</script>";
        }
    }
}

// Pagination setup
$num_results_on_page = 10; // You want 10 records per page
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1; // Current page
$start_from = ($page - 1) * $num_results_on_page; // Starting point for the query



// Query to fetch paginated results
$result = mysqli_query($con, "SELECT * FROM reviews_app LIMIT $start_from, $num_results_on_page");

// Count total records for pagination
$result_db = mysqli_query($con, "SELECT COUNT(id) FROM reviews_app");
$row_db = mysqli_fetch_row($result_db);
$total_records = $row_db[0];
$total_pages = ceil($total_records / $num_results_on_page);


?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Details</li>
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
                    <div class="card-header">
                        <h3 class="card-title">Details</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php
                        // Display success message
                        if (isset($_SESSION['message'])) {
                            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') . '</div>';
                            unset($_SESSION['message']);
                        }
                        ?>
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Message</th>
                                    <th>Rating</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = $start_from + 1;  // Display row number
                                while ($row = mysqli_fetch_array($result)) { 
                                ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo $row['rating_star']; ?></td>
                                        <td>
                                            <!-- Delete button with CSRF protection -->
                                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php
                                    $i++;
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="prev page-item"><a class="page-link" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $page - 1 ?>">Prev</a></li>
                                <?php endif; ?>

                                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $p ?>"><?php echo $p ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="next page-item"><a class="page-link" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $page + 1 ?>">Next</a></li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
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
