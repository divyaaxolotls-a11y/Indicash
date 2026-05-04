<!DOCTYPE html>
<html>
<head>
    <title>Export to PDF</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        .greenbox {
            background: green;
            padding: 7px;
            border-radius: 7px;
            color: white;
        }
        .redbox {
            background: red;
            padding: 7px;
            border-radius: 7px;
            color: white;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<button style="margin:20px;" class="pdf" onclick="generatePDF()">Export to PDF</button>

<div id="printableArea">
<?php
include('config.php');

session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "<script>
        alert('Invalid CSRF token. Please refresh the page and try again.');
        window.location.href = 'total-report.php';
    </script>";
    exit; // Stop further execution
}

// Sanitize and validate user inputs
$date = isset($_REQUEST['resultDate']) ? $_REQUEST['resultDate'] : '';
$session = isset($_REQUEST['session']) ? $_REQUEST['session'] : '';
$gameID = isset($_REQUEST['gameID']) ? $_REQUEST['gameID'] : '';

// Escape the data to prevent XSS
$date = htmlspecialchars($date, ENT_QUOTES, 'UTF-8');
$session = htmlspecialchars($session, ENT_QUOTES, 'UTF-8');
$gameID = htmlspecialchars($gameID, ENT_QUOTES, 'UTF-8');

// Further validation for date format
$date = date('d/m/Y', strtotime($date)); // Convert to desired format

$market = str_replace(" ", "_", $gameID);
$market2 = ($session == "open") ? str_replace(" ", "_", $market . ' OPEN') : str_replace(" ", "_", $market . ' CLOSE');
$marketCondition = ($session == "close") ? "(bazar='$market' OR bazar='$market2')" : "bazar='$market2'";

$total = 0;

// Fetch distinct games with placed bets
$gamesQuery = mysqli_query($con, "SELECT DISTINCT game_type FROM games WHERE date='$date' AND $marketCondition");
if (!$gamesQuery) {
    die("Games query failed: " . mysqli_error($con));
}

$games = [];
while ($gameRow = mysqli_fetch_array($gamesQuery)) {
    $games[] = $gameRow['game_type'];
}

foreach ($games as $game) {
    echo "<h4 class='game_title'>" . ucfirst(htmlspecialchars($game)) . "</h4>"; // Escape game type

    $rateQuery = mysqli_query($con, "SELECT `$game` FROM rate LIMIT 1");
    if (!$rateQuery) {
        die("Rate query failed: " . mysqli_error($con));
    }
    $rateRow = mysqli_fetch_assoc($rateQuery);
    $rate = $rateRow[$game] ?? 1;

    // Fetch total bet amount for all digits in this game type
    $digitsQuerys = mysqli_query($con, "SELECT SUM(amount) as totalbetamount FROM games WHERE amount > 0 AND date='$date' AND $marketCondition");
    $row = mysqli_fetch_assoc($digitsQuerys);
    $totalBetAmount = $row['totalbetamount'] ?? 0;

    // Fetch the digits and their amounts
    $digitsQuery = mysqli_query($con, "SELECT number, SUM(amount) as total FROM games WHERE game_type='$game' AND amount > 0 AND date='$date' AND $marketCondition GROUP BY number");

    $gameDigits = [];
    while ($digitRow = mysqli_fetch_array($digitsQuery)) {
        $digit = $digitRow['number'];
        $amount = $digitRow['total'] ?? 0;
        $winningAmount = $amount * $rate;

        $gameDigits[] = ['digit' => htmlspecialchars($digit), 'amount' => $amount, 'winning' => $winningAmount];
    }

    // Display only loss records
    echo "<div class='row'>";
    echo "<div class='container-fluid colls'>";
    echo "<div class='row'>";
    echo "<div class='col-sm titls'>";
    echo "<p>Digit</p><p>Amount</p><p>Winning</p><p>Profit/Loss</p>";
    echo "</div>";

    foreach ($gameDigits as $gameDigit) {
        $profitOrLoss = $totalBetAmount - $gameDigit['winning'];

        // Only show loss records (profitOrLoss < 0)
        $profitOrLossClass = $profitOrLoss < 0 ? 'redbox' : 'greenbox';
        $profitOrLoss = number_format($profitOrLoss, 2);

        echo "<div class='col-sm'>";
        echo "<p>" . $gameDigit['digit'] . "</p>";
        echo "<p><span class='" . ($gameDigit['amount'] == 0 ? 'redbox' : 'bluebox') . "'>" . number_format($gameDigit['amount'], 2) . "</span></p>";
        echo "<p><span class=''>" . number_format($gameDigit['winning'], 2) . "</span></p>";
        echo "<p><span class='$profitOrLossClass'>$profitOrLoss</span></p>";
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";

    // Accumulate total for this game type
    $total += array_sum(array_column($gameDigits, 'amount'));
}

echo "<h4 class='game_title'>Total Bet Amount</h4>";
echo "<div class='row'>";
echo "<div class='container-fluid colls'>";
echo "<div class='row'>";
echo "<div class='col-sm'>";
echo "<p>Total</p><p><span class='bluebox'>" . number_format($total, 2) . "</span></p>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
?>
</div>


<script>
async function generatePDF() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'pt', 'a4');

    try {
        const canvas = await html2canvas(document.getElementById('printableArea'), { scale: 2 });
        const imgData = canvas.toDataURL('image/png');
        const imgWidth = 595.28; // A4 width
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;
        let position = 20;

        pdf.addImage(imgData, 'PNG', 20, position, imgWidth - 40, imgHeight);
        heightLeft -= pdf.internal.pageSize.height;

        while (heightLeft > 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 20, position, imgWidth - 40, imgHeight);
            heightLeft -= pdf.internal.pageSize.height;
        }

        pdf.save('report.pdf');
    } catch (error) {
        console.error('Error generating PDF:', error);
    }
}
</script>

</body>
</html>
