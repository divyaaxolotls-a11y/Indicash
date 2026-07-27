<?php
include('header.php'); // DB connection ($con)

if (isset($_POST['submit'])) {

    $heading = trim($_POST['heading']);
    $link    = trim($_POST['youtube_link']);
    $order   = intval($_POST['order_no']);

    if ($heading == '' || $link == '') {
        echo "<script>alert('All fields required');</script>";
    } else {

        $heading = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
        $link    = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        mysqli_query(
            $con,
            "INSERT INTO youtube_videos (heading, youtube_link, sort_order)
             VALUES ('$heading', '$link', '$order')"
        );

        echo "<script>
            alert('YouTube link saved successfully');
            window.location.href='youtube_videos.php';
        </script>";
        exit;
    }
}
?>
<style>
.video-card {
    background:#f7f8fa;
    border-radius:12px;
    padding:15px;
}

.video-card h5 {
    margin-bottom:12px;
    font-weight:600;
}

.video-card input {
    width:100%;
    padding:10px;
    border-radius:10px;
    border:1px solid #ccc;
    margin-bottom:12px;
    font-size:15px;
}

.video-btn {
    background:#1e88e5;
    color:#fff;
    border:none;
    padding:10px 18px;
    border-radius:8px;
    font-size:15px;
}
</style>

<div class="video-card">

    <h5>Add Youtube Links</h5>

    <form method="POST">

        <input type="text" name="heading" placeholder="Heading" required>

        <input type="text" name="youtube_link" placeholder="Youtube Link" required>

        <input type="number" name="order_no" placeholder="Order">

        <button type="submit" name="submit" class="video-btn">
            Save
        </button>

    </form>

</div>

<hr>

<?php
$result = mysqli_query(
    $con,
    "SELECT * FROM youtube_videos ORDER BY sort_order ASC, id DESC"
);

while ($row = mysqli_fetch_assoc($result)) {
?>
<div style="background:#eef2f7;padding:10px;border-radius:10px;margin-bottom:8px;">
    <b><?php echo $row['heading']; ?></b><br>
    <small><?php echo $row['youtube_link']; ?></small>
</div>
<?php } ?>






