<?php include('config.php');

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
    // Alert the user and redirect back to transaction.php
    echo "<script>
        alert('Invalid CSRF token. Please refresh the page and try again.');
        window.location.href = 'sell-report.php';
    </script>";
    exit; // Stop further execution
}



// Sanitize and validate user inputs
$date2 = isset($_REQUEST['resultDate']) ? htmlspecialchars($_REQUEST['resultDate'], ENT_QUOTES, 'UTF-8') : null;
$date = date('d/m/Y', strtotime($_REQUEST['resultDate']));
$session = isset($_REQUEST['session']) ? htmlspecialchars($_REQUEST['session'], ENT_QUOTES, 'UTF-8') : null;
$type = isset($_REQUEST['type']) ? htmlspecialchars($_REQUEST['type'], ENT_QUOTES, 'UTF-8') : null;
$market = isset($_REQUEST['gameID']) ? str_replace(" ", "_", htmlspecialchars($_REQUEST['gameID'], ENT_QUOTES, 'UTF-8')) : null;



function invenDescSort($item1, $item2) {
    if ($item1['amount'] == $item2['amount']) return 0;
    return ($item1['amount'] < $item2['amount']) ? 1 : -1;
}



if ($session == "open") {
    $market2 = str_replace(" ", "_", $market . ' OPEN');
} else {
    $market2 = str_replace(" ", "_", $market . ' CLOSE');
}
$Total =0;

$singleDigitTotal =0;
$jodiDigitTotal = 0;
$pannaDigitTotal = 0;


// Calculate single digit totals
$get_data = mysqli_query($con, "SELECT * FROM `single_digit`");
$digits = [];
while ($data = mysqli_fetch_array($get_data)) {
    $query = mysqli_query($con, "SELECT SUM(amount) AS total FROM games WHERE bazar='$market2' AND number='" . $data['number'] . "' AND date='$date'");
    $total_amount = mysqli_fetch_array($query)['total'] ?? "0";
    $singleDigitTotal += (float)$total_amount;
    $digits[] = ['digit' => $data['number'], 'amount' => $total_amount];
}
usort($digits, 'invenDescSort');

// Calculate jodi digit totals
$get_data = mysqli_query($con, "SELECT * FROM `jodi_digit`");
$digits = [];
while ($data = mysqli_fetch_array($get_data)) {
    $query = mysqli_query($con, "SELECT SUM(amount) AS total FROM games WHERE bazar='$market' AND number='" . $data['number'] . "' AND date='$date'");
    $total_amount = mysqli_fetch_array($query)['total'] ?? "0";
    $jodiDigitTotal += (float)$total_amount;
    $digits[] = ['digit' => $data['number'], 'amount' => $total_amount];
}
usort($digits, 'invenDescSort');

// Calculate panna digit totals
$digits = [];
$panna_sources = ['single_pana', 'double_pana', 'triple_pana'];
foreach ($panna_sources as $source) {
    $get_data = mysqli_query($con, "SELECT * FROM `$source`");
    while ($data = mysqli_fetch_array($get_data)) {
        $query = mysqli_query($con, "SELECT SUM(amount) AS total FROM games WHERE bazar='$market2' AND number='" . $data[$source] . "' AND date='$date'");
        $total_amount = mysqli_fetch_array($query)['total'] ?? "0";
        $pannaDigitTotal += (float)$total_amount;
        $digits[] = ['digit' => $data[$source], 'amount' => $total_amount];
    }
}
usort($digits, 'invenDescSort');

$Total = $singleDigitTotal + $jodiDigitTotal + $pannaDigitTotal;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your styles here -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
    <style>
@media print {
    body {
        margin: 0;
        padding: 0;
    }
     p{ border: solid 2px red; !important}

    #printableArea {
        width: 90%;
        max-width: 100%;
        margin: 0 auto; /* Center the content horizontally */
        margin-left: 30px; /* Adjust left padding for space */
        box-sizing: border-box; /* Include padding in width calculations */
    }
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0;
    }
    .col-sm {
        flex: 1;
        min-width: 80px;
        box-sizing: border-box;
        padding: 4px;
        border: 1px solid #000; /* Add border for print */
        overflow: hidden;
    }
    .titls p {
        margin: 0;
        font-weight: bold;
    }
    .bluebox, .redbox {
        padding: 4px;
        border-radius: 3px;
        display: inline-block;
        border: 1px solid #000; /* Add border for print */
    }
}

             .titls p { font-weight: bold; }
        .redbox { color: white; }
        .bluebox { color: white; }
        .container-fluid { padding: 15px; }
        .colls { margin-bottom: 20px; }
        .game_title { font-size: 1.5em; }
        .row { margin: 0; }
                .colls p{ border: solid 1px #000; }

        
        button {
    background-color: #007bff; / Blue background /
    color: white;              / White text /
    border: none;              / Remove border /
    border-radius: 5px;       / Rounded corners /
    padding: 10px 20px;       / Padding /
    font-size: 16px;          / Font size /
    cursor: pointer;          / Pointer cursor on hover /
    transition: background-color 0.3s ease, transform 0.2s ease; / Smooth transition /
    margin: 5px;              / Space between buttons /
}

/ Button Hover Effects /
button:hover {
    background-color: #0056b3; / Darker blue on hover /
    transform: scale(1.05);   / Slightly larger on hover /
}

/ Button Focus Effect /
button:focus {
    outline: none;            / Remove default outline /
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5); / Blue glow effect /
}

/ Print Button Specific Styles /
button.print {
    background-color: #28a745; / Green background /
}

button.print:hover {
    background-color: #218838; / Darker green on hover /
}

/ PDF Button Specific Styles /
button.pdf {
    background-color: #dc3545; / Red background /
}

button.pdf:hover {
    background-color: #c82333; / Darker red on hover /
}

/ Excel Button Specific Styles /
button.excel {
    background-color: #ffc107; / Yellow background /
    color: black;              / Black text /
}

button.excel:hover {
    background-color: #e0a800; / Darker yellow on hover /
}
    </style>
</head>
<body>
    <div class="row">
        <div class="col-sm">
            <button onclick="printDiv('printableArea')">Print</button>
            <button onclick="generatePDF()">Download PDF</button>
            <button onclick="exportToExcel()" style="display:none;">Export to Excel</button>
        </div>
    </div>
    <div id="printableArea">
        <div class="row">
            <div class="container-fluid">
                <h4>Totals Summary</h4>
                <div class="row">
                       <div class="col-sm titls">
                        <p>Total</p>
                        <p><span class="bluebox"><?php echo number_format($Total, 2); ?></span></p>
                    </div>
                    
                    <div class="col-sm titls">
                        <p>Single Digit Total</p>
                        <p><span class="bluebox"><?php echo number_format($singleDigitTotal, 2); ?></span></p>
                    </div>
                    <div class="col-sm titls">
                        <p>Jodi Digit Total</p>
                        <p><span class="bluebox"><?php echo number_format($jodiDigitTotal, 2); ?></span></p>
                    </div>
                    <div class="col-sm titls">
                        <p>Panna Digit Total</p>
                        <p><span class="bluebox"><?php echo number_format($pannaDigitTotal, 2); ?></span></p>
                    </div>
                </div>
            </div>
        </div>
        
        
        
        
      <?php if ($type == "all" || $type == "single") { ?>
      <!-- Single Digit -->
      <div class="row container-fluid" style="margin-top:30px;">
        <h4 class="game_title">Single Digit</h4>
      </div>
       <div class="row">
        <div class="container-fluid colls">
            <div class="row">
                <div class="col-sm titls">
                    <p>Digit</p>
                    <p>Amount</p>
                </div>
                <?php 
                $get_data = mysqli_query($con, "SELECT * FROM `single_digit`");
                while ($data = mysqli_fetch_array($get_data)) { 
                    $query = mysqli_query($con, "SELECT SUM(amount) AS total FROM games WHERE bazar='$market2' AND number='" . $data['number'] . "' AND date='$date'");
                    if (mysqli_num_rows($query) > 0) {
                        $get_q = mysqli_fetch_array($query);
                        $total_amount = $get_q['total'] ?? "0";
                        $singleDigitTotal += (float)$total_amount;
                        $fc['digit'] = $data['number'];
                        $fc['amount'] = $total_amount;
                        $digit[] = $fc;
                    } else {
                        $fc['digit'] = $data['number'];
                        $fc['amount'] = "0";
                        $digit[] = $fc;
                    }
                }
                usort($digit, 'invenDescSort');
                for ($x = 0; $x < count($digit); $x++) {
                ?>
                <div class="col-sm">
                    <p><?php echo $digit[$x]['digit']; ?></p>
                    <p><span class="<?php echo ($digit[$x]['amount'] == "0") ? 'redbox' : 'bluebox'; ?>"><?php echo $digit[$x]['amount']; ?></span></p>
                </div>
                <?php } ?>
            </div>
        </div>
     </div>
    <?php } ?>

    <?php if ($type == "all" || $type == "jodi") { ?>
    <!-- Jodi Digit -->
    <div class="row container-fluid" style="margin-top:30px;">
        <h4 class="game_title">Jodi Digit</h4>
     </div>
     <div class="row">
        <div class="container-fluid colls">
            <div class="row">
                <div class="col-sm titls">
                    <p>Digit</p>
                    <p>Amount</p>
                </div>
                <?php 
                if (isset($digit)) { unset($digit); }
                $get_data = mysqli_query($con, "SELECT * FROM `jodi_digit`");
                while ($data = mysqli_fetch_array($get_data)) { 
                    $query = mysqli_query($con, "SELECT SUM(amount) AS total FROM games WHERE bazar='$market' AND number='" . $data['number'] . "' AND date='$date'");
                    if (mysqli_num_rows($query) > 0) {
                        $get_q = mysqli_fetch_array($query);
                        $total_amount = $get_q['total'] ?? "0";
                        $jodiDigitTotal += (float)$total_amount;
                        $fc['digit'] = $data['number'];
                        $fc['amount'] = $total_amount;
                        $digit[] = $fc;
                    } else {
                        $fc['digit'] = $data['number'];
                        $fc['amount'] = "0";
                        $digit[] = $fc;
                    }
                }
                usort($digit, 'invenDescSort');
                for ($x = 0; $x < count($digit); $x++) {
                ?>
                <div class="col-sm">
                    <p><?php echo $digit[$x]['digit']; ?></p>
                    <p><span class="<?php echo ($digit[$x]['amount'] == "0") ? 'redbox' : 'bluebox'; ?>"><?php echo $digit[$x]['amount']; ?></span></p>
                </div>
                <?php } ?>
            </div>
          </div>
       </div>
    <?php } ?>

    <?php if ($type == "all" || $type == "panna") { ?>
    <!-- Panna Digit -->
     <div class="row container-fluid" style="margin-top:30px;">
        <h4 class="game_title">Panna Digit</h4>
        </div>
         <div class="row">
           <div class="container-fluid colls">
              <div class="row">
                <div class="col-sm titls">
                    <p>Digit</p>
                    <p>Amount</p>
                </div>
                <?php 
                if (isset($digit)) { unset($digit); }
                $get_data = mysqli_query($con, "SELECT * FROM single_pana");
                while ($data = mysqli_fetch_array($get_data)) { 
                    $query = mysqli_query($con, "SELECT SUM(amount) AS total FROM games WHERE bazar='$market2' AND number='" . $data['single_pana'] . "' AND date='$date'");
                    if (mysqli_num_rows($query) > 0) {
                        $get_q = mysqli_fetch_array($query);
                        $total_amount = $get_q['total'] ?? "0";
                        $pannaDigitTotal += (float)$total_amount;
                        $fc['digit'] = $data['single_pana'];
                        $fc['amount'] = $total_amount;
                        $digit[] = $fc;
                    } else {
                        $fc['digit'] = $data['single_pana'];
                        $fc['amount'] = "0";
                        $digit[] = $fc;
                    }
                }
                usort($digit, 'invenDescSort');
                for ($x = 0; $x < count($digit); $x++) {
                ?>
                <div class="col-sm">
                    <p><?php echo $digit[$x]['digit']; ?></p>
                    <p><span class="<?php echo ($digit[$x]['amount'] == "0") ? 'redbox' : 'bluebox'; ?>"><?php echo $digit[$x]['amount']; ?></span></p>
                </div>
                <?php } ?>
            </div>
           </div>
        </div>
      <?php } ?>
    
   
    <script>
    async function generatePDF() {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'pt', 'a4'); // Portrait mode

        html2canvas(document.getElementById('printableArea')).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const imgWidth = 595.28; // Width of A4 in points (portrait)
            const imgHeight = canvas.height * imgWidth / canvas.width;
            let heightLeft = imgHeight;

            const margin = 20; // Margin in points
            let position = margin;

            pdf.addImage(imgData, 'PNG', margin, position, imgWidth - 2 * margin, imgHeight);
            heightLeft -= pdf.internal.pageSize.height;

            while (heightLeft > 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', margin, position, imgWidth - 2 * margin, imgHeight);
                heightLeft -= pdf.internal.pageSize.height;
            }

            pdf.save('report.pdf');
        });
      }

    function printDiv(divName) {
    var printContents = document.getElementById(divName).innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
   }


    function exportToExcel() {
        const wb = XLSX.utils.book_new();
        
        // Create table data
        let tableData = [];
        document.querySelectorAll('#printableArea .row').forEach(row => {
            let rowData = [];
            row.querySelectorAll('.col-sm').forEach(cell => {
                rowData.push(cell.innerText.trim());
            });
            if (rowData.length > 0) {
                tableData.push(rowData);
            }
        });

        // Convert table data to worksheet
        const ws = XLSX.utils.aoa_to_sheet(tableData);

        // Append the worksheet to the workbook
        XLSX.utils.book_append_sheet(wb, ws, "Report");

        // Write the workbook to a file
        XLSX.writeFile(wb, "report.xlsx");
    }
    </script>
</body>
</html>
