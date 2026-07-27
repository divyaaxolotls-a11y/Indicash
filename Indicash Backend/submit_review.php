<?php
// Database connection
$servername = "localhost";
$username = "dmboss";
$password = "Zrs37reJ3H35tJBf";
$dbname = "dmboss";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     // Retrieve form data
//     $name = mysqli_real_escape_string($conn, $_POST['name']);
//     $email = mysqli_real_escape_string($conn, $_POST['email']);
//     $message = mysqli_real_escape_string($conn, $_POST['message']);
//     $rating_star = intval($_POST['rating_star']); // Ensure it's an integer

//     // Insert data into the reviews table
//     $sql = "INSERT INTO reviews_app (name, email, message, rating_star) VALUES ('$name', '$email', '$message', '$rating_star')";

//     if (mysqli_query($conn, $sql)) {
//         // Redirect back to the reviews page after successful submission
//         header("Location: {$_SERVER['HTTP_REFERER']}");
//         exit(); // Make sure to exit after the redirect to stop further execution
//     } else {
//         echo "Error: " . $sql . "<br>" . mysqli_error($conn);
//     }
// }

// Close connection
mysqli_close($conn);
?>
