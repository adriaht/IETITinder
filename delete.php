<?php

    $file = __DIR__ . "/imgPrueba/user1_photo1.jpg";
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "File deleted successfully.";
        } else {
            echo "Error deleting the file.";
        }
    } else {
        echo "File does not exist.";
    }

?>