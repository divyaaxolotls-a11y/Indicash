<?php include('header.php'); ?>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    * { box-sizing: border-box; }

    .filter-container {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 12px 0;
        padding: 0 8px;
    }
    .row-select {
        width: 70px;
        padding: 7px 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        outline: none;
        font-size: 13px;
        flex-shrink: 0;
    }
    .search-input {
        flex-grow: 1;
        padding: 7px 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        outline: none;
        font-size: 13px;
        min-width: 0;
    }

    /* Wrapper: relative so sticky thead works */
    .table-wrap {
        width: 100%;
        overflow-x: hidden;         /* no horizontal scroll */
        overflow-y: auto;
        max-height: 70vh;           /* vertical scroll for body only */
        border: 1px solid #ddd;
        border-radius: 6px;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;        /* fixed so % widths hold on mobile */
    }

    /* STICKY header — stays visible while body scrolls vertically */
    .custom-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #f39c12;
        color: #fff;
        text-align: center;
        padding: 11px 4px;
        border: 1px solid #e67e22;
        font-weight: 600;
        font-size: 12px;
        word-break: break-word;
        white-space: normal;
    }

    .custom-table tbody td {
        text-align: center;
        padding: 10px 4px;
        border: 1px solid #ddd;
        font-size: 12px;
        color: #000;
        word-break: break-word;
        white-space: normal;
        line-height: 1.3;
        background: #fff
    }

    /*.custom-table tbody tr:nth-child(even) td { background: #fafafa; }*/
    .custom-table tbody tr:hover td { background: #fff8e1; }

    /* Column widths — all 6 fit the full screen width */
    .col-sno   { width: 10%; }
    .col-user  { width: 28%; }
    .col-call  { width: 15%; }
    .col-wp    { width: 15%; }
    .col-point { width: 16%; }
    .col-idays { width: 16%; }

    @media (max-width: 400px) {
        .custom-table thead th { font-size: 10px; padding: 8px 2px; }
        .custom-table tbody td  { font-size: 10px; padding: 8px 2px; }
        .filter-container { gap: 6px; margin: 8px 0; padding: 0 4px; }
        .row-select  { width: 60px; font-size: 12px; }
        .search-input { font-size: 12px; }
    }
</style>

<div class="container-fluid px-2">

    <div class="table-wrap">
        <table class="custom-table">
            <thead>
                <tr>
                    <th class="col-sno">S.No</th>
                    <th class="col-user">Username</th>
                    <th class="col-call">Call</th>
                    <th class="col-wp">WP</th>
                    <th class="col-point">Point</th>
                    <th class="col-idays">Idays</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $i = 1;
                    // $sql = "SELECT * FROM users ORDER BY created_at  ASC";
                    // $users = mysqli_query($con,$sql);
                    $sql = "SELECT u.*, 
                              (SELECT MAX(created_at) FROM games WHERE user = u.mobile) as last_bet_time 
                            FROM users u 
                            ORDER BY (CASE WHEN last_bet_time IS NULL THEN u.created_at ELSE last_bet_time END) ASC";
                    
                    $users = mysqli_query($con, $sql);
                    $today = time();

                    while($row = mysqli_fetch_assoc($users)){
                        $inactiveDays = 0;
                         $referenceTime = !empty($row['last_bet_time']) ? $row['last_bet_time'] : $row['created_at'];

                        if(!empty($referenceTime)){
                            $refTimestamp = is_numeric($referenceTime) ? $referenceTime : strtotime($referenceTime);
                            $inactiveDays = floor(($today - $refTimestamp) / 86400);
                        }
                
                        // Only show users inactive for 1 day or more
                        // If Amol Bhai played today, $inactiveDays will be 0 and he will be HIDDEN from this list
                        if($inactiveDays < 1) continue; 
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td class="open-admin-action" 
                            data-name="<?php echo $row['name']; ?>" 
                            data-mobile="<?php echo $row['mobile']; ?>" 
                            >
                            <?php echo $row['name']; ?>
                        </td>
                        <!--<td><?php echo $row['name']; ?></td>-->
                        <!--<td><?php echo $row['mobile']; ?></td>-->
                         <!-- 3. Call (Mobile link) -->
                        <td>
                            <a href="tel:<?php echo $row['mobile']; ?>" style="color:#007bff; font-weight:bold; text-decoration:none;">Call</a>
                        </td>
                        
                        <!-- 4. WP (WhatsApp link) -->
                        <td>
                            <a href="https://wa.me/91<?php echo $row['mobile']; ?>" target="_blank" style="color:#28a745; font-weight:bold; text-decoration:none;">WP</a>
                        </td>
                        
                        <!-- 5. Point (Using 'wallet' from your DB) -->
                        <td style="font-weight:bold;">
                            <?php echo isset($row['wallet']) ? $row['wallet'] : '0'; ?>
                        </td>
                        <td><?php echo $inactiveDays; ?></td>
                    </tr>
                    <?php } ?>
            </tbody>
        </table>
    </div>

</div>
<!-- Admin Action Modal -->
<div class="modal fade" id="adminActionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:25px; overflow:hidden;">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold" id="actionNameHeader">User Actions</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <a href="#" id="linkAdd" class="btn btn-success p-3" style="border-radius:15px;"><i class="fas fa-plus-circle"></i><br>Add Money</a>
                    <a href="#" id="linkWithdraw" class="btn btn-danger p-3" style="border-radius:15px;"><i class="fas fa-minus-circle"></i><br>Withdraw</a>
                    <a href="#" id="linkPL" class="btn btn-info p-3" style="border-radius:15px;"><i class="fas fa-chart-line"></i><br>Profit/Loss</a>
                    <a href="#" id="linkNotify" class="btn btn-warning p-3" style="border-radius:15px;"><i class="fas fa-bell"></i><br>Notify</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.open-admin-action', function() {
    var name = $(this).data('name');
    var mobile = $(this).data('mobile');
    $('#actionNameHeader').text('Manage: ' + name);
    $('#linkAdd').attr('href', 'add-money-manual.php?mobile=' + mobile);
    $('#linkWithdraw').attr('href', 'withdraw-money-manual.php?mobile=' + mobile);
    $('#linkPL').attr('href', 'transaction.php?mobile=' + mobile);
    $('#linkNotify').attr('href', 'send-notification.php?mobile=' + mobile);
    $('#adminActionModal').modal('show');
});
</script>
<?php include('footer.php'); ?>