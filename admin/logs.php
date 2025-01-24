<?php
// Init sessión
session_start();

include("../functions.php"); /* Loads search from users + logs + startPDO */ 

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

if ($loggedUser['role'] !== "admin") {
    http_response_code(403);
    exit;
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

            <div id="title-container">
                <h2>Visualitzador de logs</h2>
            </div>

            <div id="table-container">

                <table>

                    <thead>
                        <tr>
                            <th>ID usuari</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Última conexió</th>
                            <th>Validat</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Maria López</td>
                            <td>maria.lopez@example.com</td>
                            <td>Administrador</td>
                            <td>2025-01-23 14:32</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Joan Ferrer</td>
                            <td>joan.ferrer@example.com</td>
                            <td>Editor</td>
                            <td>2025-01-22 09:15</td>
                            <td>No</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>
                        <tr>
                            <td>25</td>
                            <td>Laura Vidal</td>
                            <td>laura.vidal@example.com</td>
                            <td>Usuari</td>
                            <td>2025-01-24 18:47</td>
                            <td>Sí</td>
                            <td><a href="#">Veure més</a></td>
                        </tr>

                    </tbody>

                </table>

            </div>

            <div id="pagination">

                <ul>
                    <li class="disabled"><a href="#">←</a></li>
                    <li class="active"><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#">4</a></li>
                    <li><a href="#">5</a></li>
                    <li><a href="#">→</a></li>
                </ul>

            </div>

        </div>
       
    </div>

</body>
</html>

