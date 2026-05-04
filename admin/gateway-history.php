<?php 
include('header.php'); 

// 1. Get Filter Inputs
$sel_user   = $_GET['username'] ?? '';
$sel_date = $_GET['date'] ?? date('Y-m-d');
$sel_status = $_GET['status'] ?? '';
$search     = $_GET['search'] ?? '';

$limit      = 20;
$page       = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset     = ($page - 1) * $limit;

// 2. Query Logic (Table: payments)
$where = " WHERE 1=1";
if(!empty($sel_user))   { $where .= " AND name LIKE '%".mysqli_real_escape_string($con, $sel_user)."%'"; }
if(!empty($sel_date))   { $where .= " AND DATE(created_at) = '".mysqli_real_escape_string($con, $sel_date)."'"; }
if(!empty($sel_status)) { $where .= " AND status = '".mysqli_real_escape_string($con, $sel_status)."'"; }
if(!empty($search)) { 
    $s = mysqli_real_escape_string($con, $search);
    $where .= " AND (order_id LIKE '%$s%' OR mobile LIKE '%$s%')"; 
}

$summary_sql = "SELECT 
    SUM(CASE WHEN status = 'SUCCESS' THEN amount ELSE 0 END) as s_amt,
    COUNT(CASE WHEN status = 'SUCCESS' THEN 1 END) as s_count,
    SUM(CASE WHEN status = 'PENDING' THEN amount ELSE 0 END) as p_amt,
    COUNT(CASE WHEN status = 'PENDING' THEN 1 END) as p_count,
    COUNT(*) as total_rows FROM payments $where";

$summary_res = mysqli_query($con, $summary_sql);
$summary = mysqli_fetch_assoc($summary_res);
$total_pages = ceil(($summary['total_rows'] ?? 0) / $limit);
if($total_pages < 1) $total_pages = 1;

$data_sql = "SELECT * FROM payments $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($con, $data_sql);
?>

<style>
    /* Content Area Background */
    .content-wrapper { background-color: #f8f9fa !important; }

    /* Container: Wider on desktop, full on mobile */
    .history-container { 
        padding: 20px; 
        width: 100%; 
        max-width: 900px; /* Increased from 600px for desktop */
        margin: auto; 
        min-height: 80vh;
        padding-bottom: 100px; /* Space for the fixed nav */
    }
    
    .label-text { font-size: 14px; color: #555; margin-bottom: 5px; font-weight: bold; }
    
    .custom-input {
        width: 100%; height: 45px; border-radius: 10px; border: 1px solid #ced4da;
        padding: 0 15px; background: #fff; margin-bottom: 15px; font-size: 15px; outline: none;
    }

    .filter-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

    .btn-filter {
        width: 100%; background: #007bff; color: white; border: none; height: 45px;
        border-radius: 10px; font-weight: bold; font-size: 16px; cursor: pointer;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* Summary Bars */
    .orange-bar {
        background: #ffa500; color: #000; text-align: center; padding: 12px;
        font-weight: bold; border-radius: 8px; margin: 15px 0; font-size: 15px;
    }
    .summary-bar { background: #ffb732; margin-top: -10px; font-size: 14px; }

    /* Results */
    .pay-card {
        background: #fff; border-radius: 12px; padding: 15px; margin-bottom: 12px;
        border-left: 6px solid #ccc; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .pay-card.SUCCESS { border-left-color: #28a745; }
    .pay-card.PENDING { border-left-color: #ffc107; }
    
    .card-row { display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 8px; }
    .status-badge { font-size: 11px; padding: 3px 12px; border-radius: 20px; color: white; text-transform: uppercase; }
    .badge-SUCCESS { background: #28a745; }
    .badge-PENDING { background: #ffc107; color: black; }

    /* FIXING THE BOTTOM NAV FOR ADMIN PANEL */
    .sticky-pagination {
        position: fixed;
        bottom: 0;
        left: 0; /* Changed to 0 */
        right: 0;
        background: #f8f9fa;
        display: flex;
        padding: 10px 15px;
        gap: 10px;
        z-index: 9999; /* Higher z-index to stay above footer */
        border-top: 1px solid #ddd;
        justify-content: center;
    }
    
    /* On desktop, shift it to the right to account for the sidebar */
    @media (min-width: 768px) {
        .sticky-pagination {
            left: 250px; /* Adjust this to match your sidebar width exactly */
        }
    }

    .nav-btn {
        flex: 1; max-width: 300px; height: 45px; border: none; border-radius: 10px; color: white;
        font-weight: bold; font-size: 14px; display: flex; align-items: center;
        justify-content: center; text-decoration: none !important;
    }
    .btn-teal { background: #76d7c4; color: #000 !important; } /* Matching your screenshot color */
    .btn-dark-grey { background: #5d6d7e; color: white; pointer-events: none; }
      .pay-card {
        border-radius: 15px; padding: 15px; margin-bottom: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative;
        border: 1px solid #ddd;
    }
    /* Card Colors based on screenshot */
    .pay-card.SUCCESS { background-color: #f1fdf4; border-left: 6px solid #28a745; }
    .pay-card.PENDING { background-color: #ffeed3; border-left: 6px solid #f39c12; }
    
    .card-title-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: bold; font-size: 15px; }
    
    .btn-copy-teal {
        background: #17a2b8; color: white !important; border-radius: 10px;
        padding: 5px 18px; font-size: 14px; border: none; cursor: pointer;
    }
    
    .amount-line { font-size: 15px; color: #444; margin-bottom: 10px; }
    
    .btn-status-blue {
        background: #007bff; color: white !important; border-radius: 20px;
        padding: 6px 20px; font-size: 13px; display: inline-block; text-decoration: none; border: none;
    }
</style>

<div class="history-container">
    <form method="get">
        <div class="filter-grid">
            <div>
                <label class="label-text">Date</label>
                <input type="date" name="date" class="custom-input" value="<?php echo $sel_date; ?>">
            </div>
            <div>
                <label class="label-text">Status</label>
                <select name="status" class="custom-input">
                    <option value="">Status</option>
                    <option value="SUCCESS" <?php if($sel_status == 'SUCCESS') echo 'selected'; ?>>SUCCESS</option>
                    <option value="PENDING" <?php if($sel_status == 'PENDING') echo 'selected'; ?>>PENDING</option>
                </select>
            </div>
        </div>

        <div class="filter-grid">
            <input type="text" name="username" class="custom-input" placeholder="Name" value="<?php echo htmlspecialchars($sel_user); ?>">
            <input type="text" name="search" class="custom-input" placeholder="Order ID, Phone, User" value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <button type="submit" class="btn-filter">Filter</button>
    </form>

    <div class="orange-bar">GateWay Payment History</div>
    <div class="orange-bar summary-bar">
        Success Payment :<?php echo number_format($summary['s_amt'] ?? 0, 2); ?>-(<?php echo $summary['s_count']; ?>), 
        Pending Payment :<?php echo number_format($summary['p_amt'] ?? 0, 2); ?>-(<?php echo $summary['p_count']; ?>)
    </div>

    <div class="results-list">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php 
                $count = $offset + 1; // Initialize counter
                while($row = mysqli_fetch_assoc($result)): 
                    // Prepare data for the copy function
                    $formattedDate = date('d-m-Y h:i A', strtotime($row['created_at']));
                    $copyText = "order id : " . $row['order_id'] . "\\n" .
                                "Transaction id : " . ($row['payment_id'] ?? '') . "\\n" .
                                "Amount : " . (int)$row['amount'] . "\\n" .
                                "date : " . $formattedDate . "\\n" .
                                "Status : " . strtolower($row['status']);
                ?>
                    <div class="pay-card <?php echo $row['status']; ?>">
                        <div class="card-title-row">
                            <span>(<?php echo $count++; ?>) <?php echo htmlspecialchars($row['name']); ?> - <?php echo $row['mobile']; ?></span>
                            <button class="btn-copy-teal" onclick="copyFormatted('<?php echo $copyText; ?>')">Copy</button>
                        </div>
                        
                        <div class="amount-line">Amount - <?php echo (int)$row['amount']; ?></div>
                        
                        <?php if($row['status'] == 'PENDING'): ?>
                            <a href="#" class="btn-status-blue">Check status</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center text-muted" style="margin-top: 50px;">No records found.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Bottom Navigation Fixed to Content -->
<div class="sticky-pagination">
    <?php $base = "?username=".urlencode($sel_user)."&date=".urlencode($sel_date)."&status=".urlencode($sel_status)."&search=".urlencode($search); ?>

    <?php if($page > 1): ?>
        <a href="<?php echo $base; ?>&page=<?php echo $page-1; ?>" class="nav-btn btn-teal">PERVS</a>
    <?php else: ?>
        <a href="javascript:void(0)" class="nav-btn btn-teal" style="opacity:0.5;">PERVS</a>
    <?php endif; ?>

    <div class="nav-btn btn-dark-grey"><?php echo $page; ?> / <?php echo $total_pages; ?></div>

    <?php if($page < $total_pages): ?>
        <a href="<?php echo $base; ?>&page=<?php echo $page+1; ?>" class="nav-btn btn-teal">NEXT</a>
    <?php else: ?>
        <a href="javascript:void(0)" class="nav-btn btn-teal" style="opacity:0.5;">NEXT</a>
    <?php endif; ?>
</div>
<script>
function copyFormatted(text) {
    // Create a temporary textarea to handle newlines correctly
    const textArea = document.createElement("textarea");
    textArea.value = text.replace(/\\n/g, '\n'); // Convert literal \n to real newlines
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        alert('Transaction Details Copied!');
    } catch (err) {
        console.error('Unable to copy', err);
    }
    document.body.removeChild(textArea);
}
</script>
<?php include('footer.php'); ?>