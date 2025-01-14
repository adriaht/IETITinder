
<?php
// Init sessión
session_start();


// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Discover</title>
    <link rel="stylesheet" type="text/css" href="styles.css?t=<?php echo time();?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="messages.js"></script>
</head>
<body class="body">

    <div class="container">

        <div class="card">

            <header>
                <p class="logo">IETinder ❤️</p>
                <p>Cercar</p>
            </header>

            <main id="content" class="content messages">
                <div class="container-without-messages">
                    <h2>Els meus matches</h2>
                    <div class="no-matches-without-messages">
                        <p>Hi ha gent esperant per parlar amb tu.</p>
                        <p>Torna'ls el like per començar a xatejar</p>
                    </div>
                </div>
                <div class="container-with-messages">
                    <h2>Missatges</h2>
                    <div class="no-matches-with-messages">
                        <p>No hi ha cap conversa,</p>
                        <p>descobreix gent nova i fes match</p>
                    </div>
                </div>
            </main>

            <nav>
                <ul>
                    <li><a href="/discover.php">Descobrir</a></li>
                    <li><a href="/messages.php">Missatges</a></li>
                    <li><a href="/profile.php">Perfil</a></li>
                </ul>
            </nav>
        
        </div>
        
    </div>

</body>
</html>

