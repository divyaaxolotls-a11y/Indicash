<?php 
include('header.php'); 
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$u_search = isset($_GET['user_search']) ? mysqli_real_escape_string($con, $_GET['user_search']) : '';
$text_search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';

// Handle Sticky Search Label
$user_display_info = "";
if($u_search != "") {
    $u_info_res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$u_search'");
    $u_info_data = mysqli_fetch_assoc($u_info_res);
    $user_display_info = ($u_info_data['name'] ?? 'User') . " ($u_search)";
}

// Build Query
$where = "WHERE 1=1";
if ($u_search != '') {
    $where .= " AND mobile = '$u_search'";
} elseif ($text_search != '') {
    $where .= " AND (name LIKE '%$text_search%' OR mobile LIKE '%$text_search%' OR device_id LIKE '%$text_search%')";
}

$query = mysqli_query($con, "SELECT * FROM users $where ORDER BY sn DESC LIMIT $limit");

if (in_array(6, $HiddenProducts)){  
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Full page reset */
    body, html { background-color: #ffffff; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
    
    .full-page-container { width: 100%; padding: 0; margin: 0; }

    /* Top Filter Bar - Full Width */
    .filter-section {
        padding: 10px;
        background: #fff;
        display: flex;
        gap: 10px;
        align-items: center;
        border-bottom: 1px solid #eee;
    }
    
    .custom-select-sm { width: 80px; border-radius: 5px; height: 35px; border: 1px solid #ccc; }
    .custom-search-input { flex-grow: 1; border-radius: 5px; height: 35px; border: 1px solid #ccc; padding-left: 10px; }

    /* THE BLUE ADMIN HEADER - 7 Columns */
    .admin-header-row {
        background-color: #1e69de; 
        color: white;
        display: flex;
        padding: 12px 2px;
        width: 100%;
        align-items: center;
    }

    .admin-col {
        flex: 1;
        font-size: 10px; /* Reduced for 7 columns */
        font-weight: 600;
        text-align: center;
        border-right: 1px solid rgba(255, 255, 255, 0.2);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-col:last-child { border-right: none; }

    /* Column Width Management */
    .col-sn     { flex: 0.4; }
    .col-ph     { flex: 0.8; }
    .col-user   { flex: 1.2; }
    .col-model  { flex: 1.1; }
    .col-id     { flex: 0.9; }
    .col-date   { flex: 1; }
    .col-status { flex: 0.8; }

    /* Content Area */
    .pages-label {
        width: 100%;
        text-align: center;
        padding-top: 50px;
        font-size: 24px;
        color: #444;
        font-weight: 500;
    }
.select2-container .select2-selection--single { height: 38px !important; border-radius: 20px !important; border: 1px solid #ccc !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 15px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }

    .search-row { display: flex; gap: 8px; margin-bottom: 8px; }
    .search-row > div { flex: 1; min-width: 0; }

    #report-data { width: 100%; }

    /* Responsive adjustment for very small screens */
    @media (max-width: 350px) {
        .admin-col { font-size: 9px; }
    }
</style>

<div class="full-page-container">
    <form method="get">
    <div class="filter-section">
        <select name="limit" class="custom-select-sm" onchange="this.form.submit()">
            <option value="100" <?php if($limit == 100) echo 'selected'; ?>>100</option>
            <option value="500" <?php if($limit == 500) echo 'selected'; ?>>500</option>
            <option value="1000" <?php if($limit == 1000) echo 'selected'; ?>>1000</option>
        </select>
        
        <div style="flex-grow: 1;">
            <select name="user_search" id="user_search_ajax" onchange="this.form.submit()">
                <?php if($u_search != ""): ?>
                    <option value="<?php echo $u_search; ?>" selected><?php echo $user_display_info; ?></option>
                <?php else: ?>
                    <option value="">Search Mobile or Name...</option>
                <?php endif; ?>
            </select>
        </div>

        <?php if($u_search != ""): ?>
            <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="btn btn-sm btn-light border"><i class="fa fa-times text-danger"></i></a>
        <?php endif; ?>
        
        <button type="submit" style="border:none; background:none;"><i class="fa fa-refresh" style="font-size: 18px; color: #555;"></i></button>
    </div>
</form>

    <div class="admin-header-row">
        <div class="admin-col col-sn">SN</div>
        <div class="admin-col col-ph">ph</div>
        <div class="admin-col col-user">username</div>
        <div class="admin-col col-model">Brand Model</div>
        <div class="admin-col col-id">Device id</div>
        <div class="admin-col col-date">Date</div>
        <div class="admin-col col-status">Status</div>
    </div>

    <div id="report-data">
    <?php 
    $i = 1;
    if(mysqli_num_rows($query) > 0) {
        while($row = mysqli_fetch_assoc($query)) { 
    ?>
    <div class="admin-header-row data-row" style="background: #fff; color: #333; border-bottom: 1px solid #f1f1f1;">
        <div class="admin-col col-sn" style="color:#1e69de; font-weight:bold;"><?php echo $i++; ?></div>
        <div class="admin-col col-ph"><?php echo $row['mobile']; ?></div>
        <div class="admin-col col-user" style="font-weight:600; text-align:left; padding-left:5px;"><?php echo $row['name']; ?></div>
        <div class="admin-col col-model"><?php echo $row['device_brand'] . ' ' . $row['device_model']; ?></div>
        <div class="admin-col col-id"><?php echo substr($row['device_id'], -8); ?>...</div> <!-- Shows last 8 chars of ID -->
        <div class="admin-col col-date" style="font-size:9px; line-height:1;"><?php echo str_replace(' ', '<br>', $row['last_login_time']); ?></div>
        <div class="admin-col col-status">
            <span style="background: <?php echo ($row['active'] == 1) ? '#28a745' : '#dc3545'; ?>; color:#fff; padding:2px 8px; border-radius:10px; font-size:9px;">
                <?php echo ($row['active'] == 1) ? 'active' : 'banned'; ?>
            </span>
        </div>
    </div>
    <?php 
        } 
    } else {
        echo '<div class="pages-label" style="font-size:16px;">No Records Found</div>';
    }
    ?>
</div>
</div>

<script>
$(document).ready(function() {
    // Live Search Functionality
    $('#filter_search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $(".data-row").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // 1. Initialize AJAX Search (Select2)
    $('#user_search_ajax').select2({
        width: '100%',
        placeholder: "Search Mobile or Name...",
        minimumInputLength: 2,
        ajax: {
            url: 'user-search-live.php', // Ensure this file exists from your previous setup
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return { results: data }; },
            cache: true
        }
    });

    // 2. Simple Filter Search for current visible rows
    $('#filter_search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $(".data-row").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
<?php 
} else { 
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}
include('footer.php'); 
?>