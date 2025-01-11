<?php
// discober.php
session_start();
if (!isset($_SESSION['user']['id'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discober</title>
</head>
<body>
    <h1>You Logged In</h1>
</body>
</html>