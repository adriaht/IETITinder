<?php
// Init sessión
session_start();

include("../functions.php"); /* Loads search from users + logs + startPDO */ 

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    logOperation("[LOGS.PHP] User is not logged in");
    header('Location: /login.php');
    exit;
}

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

if ($loggedUser['role'] !== "admin") {
    logOperation("[LOGS.PHP] User is not an admin. Sent error 403.");
    header("HTTP/1.1 403 Forbidden");
    include("../errors/error403.php");
    die();
}




?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Panell d'Administració</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time();?>" />
    
</head>
<body class="admin-panel">

    <div class="dashboard">

        <div id="navigation">

            <header id="header">
                <p class="logo">IETinder ❤️</p>
            </header>

            <nav>
                <ul>
                    <li><a href="/admin/index.php">Principal</a></li>
                    <li><a href="/admin/users.php">Usuaris</a></li>
                    <li class="active"><a href="/admin/logs.php">Registres</a></li>
                    <li><a href="/index.php">Tornar a l'inici</a></li>
                </ul>
            </nav>

        </div>

        <div id="content">

        <?php $dir = "../logs"; ?>
        <?php if(!isset($_GET["id"])) { 

            logOperation("[LOGS.PHP] GET id not set, displaying all log files.");

            ?>
            
            <div id="title-container">
                <h2>Administració de logs</h2>
            </div>

            <div id="table-container">

            <table class="logs">

                <thead>
                    <tr>
                        <th>Nom d'arxiu</th>
                        <th>Tamany (bytes)</th>
                        <th>Línies</th>
                        <th>Data d'última modificació</th>
                        <th></th>
                    </tr>
                </thead>

                <?php  
                    
                   
                    $files = glob($dir . "/*.txt");

                    logOperation("[LOGS.PHP] Successfully got log files from $dir.");

                    usort($files, function($a, $b) {
                        return filemtime($b) - filemtime($a);
                    });

                    logOperation("[LOGS.PHP] Successfully sorted log files by last modified time.");

                    $perPage = 25;
                    $quantityOfPages = ceil(count($files) / $perPage);
                    
                    $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
                
                    if ($currentPage < 1) {
                        $currentPage = 1;
                    } 
                
                    if ($currentPage > $quantityOfPages) {
                        $currentPage = $quantityOfPages;
                    }

                    $paginatedFiles = array_slice($files, ($currentPage - 1) * $perPage,  $perPage);
                ?>

                <tbody>

                    <?php foreach ($paginatedFiles as $file) { ?> 
                    <tr>
                        <td><?php echo basename($file); ?></td>
                        <td><?php echo filesize($file); ?></td>
                        <td><?php echo count(file($file)); ?></td>
                        <td><?php echo date("Y-m-d H:i:s", filemtime($file)); ?></td>
                        <td><a href='?id=<?php echo basename($file)?>'>Veure detalls</a></td>
                    </tr>
                    <?php } 

                    logOperation("[LOGS.PHP] Successfully printed log files in table");

                    ?>

                </tbody>
            </table>

            </div>

            <div id="pagination">

                <ul>    
                    <?php 

                        // PREVIOUS PAGE ( << )
                        if ($currentPage > 1) {
                            $previousPage = $currentPage - 1;
                            echo "<li><a href='?page=$previousPage'>&laquo;</a></li>";
                        }

                        // NUMBER GENERATION
                        $maxShownPages = 5; // Max anchors (max. 5)
                        $initialPage = max(1, $currentPage - floor($maxShownPages / 2)); // Dinamic initial paging (HIGHEST OF --> 1 or (currentPage - 2))
                        $endingPage = min($quantityOfPages, $initialPage + $maxShownPages - 1); // Dinamic initial paging (LOWEST OF --> total pages or (currentPage + 2))

                        // Anchor generation
                        for ($i = $initialPage; $i <= $endingPage; $i++) {
                            if ($i == $currentPage) {
                                echo "<li class='active'><a href='?page=$i'>$i</a></li>"; // Página actual
                            } else {
                                echo "<li><a href='?page=$i'>$i</a></li>";
                            }
                        }

                        // NEXT PAGE ( >> )
                        if ($currentPage < $quantityOfPages) {
                            $nextPage = $currentPage + 1;
                            echo "<li><a href='?page=$nextPage'>&raquo;</a></li>";
                        }

                    ?>
                </ul>    

            </div>
                            
        <?php } else { 

                    $file = $dir."/".$_GET["id"];
                    // echo $file;
                    if(file_exists($file)) { 

                        logOperation("[LOGS.PHP] ".$_GET["id"]." exists, loading data");
                        
                        ?>
                        
                         <div id="title-container">
                            <h1><a href="/admin/logs.php">&#8592;</a> Administració del log <?php echo htmlspecialchars($_GET["id"]); ?></h1>
                        </div> 

                        <div id="log-content">

                            <div id="log-metadata">

                                <h2>Metadades</h2>  
                                <p><strong>Nom: </strong><?php echo basename($file); ?></p>
                                <p><strong>Tamany: </strong><?php echo filesize($file);?> bytes</p>
                                <p><strong>Línies: </strong><?php echo count(file($file));  ?></p>
                                <p><strong>Data d'última modificació: </strong><?php echo date("Y-m-d H:i:s", filemtime($file)); ?></p>
                             
                            </div>

                            <div id="log-print">

                                <?php
                                 $content = file_get_contents($file);
                                 echo nl2br($content);
                                ?>

                            </div>

                        </div>
                        

                    <?php } else {

                        logOperation("[LOGS.PHP] ".$_GET["id"]." doesn't exist, returned to logs.php");
                        header('Location: /admin/logs.php');

                    } ?>

            <?php } ?>

        </div>
       
    </div>

</body>
</html>

