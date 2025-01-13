<?php
// discover.php
session_start();
if (!isset($_SESSION['user']['id'])) {
    echo 'hola';
}
?>
