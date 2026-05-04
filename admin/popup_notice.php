<?php
include('header.php'); // DB connection $con

if (isset($_POST['submit'])) {

    $title   = trim($_POST['title']);
    $message = trim($_POST['message']);

    if ($title == '' || $message == '') {
        echo "<script>alert('All fields required');</script>";
    } else {

        $title   = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        mysqli_query($con,
            "INSERT INTO popup_notice (title, message)
             VALUES ('$title', '$message')"
        );

        echo "<script>
            alert('Popup notice saved successfully');
            window.location.href='popup_notice.php';
        </script>";
        exit;
    }
}
?>
<style>
.popup-card {
    background:#f7f8fa;
    padding:15px;
    border-radius:12px;
}

.popup-card input,
.popup-card textarea {
    width:100%;
    padding:10px;
    border-radius:10px;
    border:1px solid #ccc;
    margin-bottom:12px;
}

.popup-btn {
    background:#1e88e5;
    color:#fff;
    border:none;
    padding:10px 18px;
    border-radius:8px;
}
</style>

<div class="popup-card">

<form method="POST">

    <label>Title</label>
    <input type="text" name="title" required>

    <label>Popup Message</label>
    <textarea name="message" rows="5" required></textarea>

    <button type="submit" name="submit" class="popup-btn">
        Submit
    </button>

</form>

</div>


<?php
$result = mysqli_query(
    $con,
    "SELECT * FROM popup_notice ORDER BY id DESC"
);

while ($row = mysqli_fetch_assoc($result)) {
?>
    <div class="popup-card" style="margin-top:10px;">

        <p><b>Title :</b> <?php echo $row['title']; ?></p>

        <p><b>Message :</b><br>
            <?php echo nl2br($row['message']); ?>
        </p>

    </div>
<?php } ?>







