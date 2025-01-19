<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Descobrir</title>
    <link rel="stylesheet" type="text/css" href="fotos.css?t=<?php echo time();?>" />
    <script src="fotosUpload.js"></script>
</head>
<body>

    <div class="container">

        <div class="card">

        
            <header id="header">
                <p class="logo">IETinder ❤️</p>

            </header>

            <main id="content" class="discover content">
                <div id="photos-content">
                    <h2>Les meves fotos</h2>
                    <div id="photos-container">
                        <div>
                            <img src="../images/user1_photo1.jpg" alt="">
                            <button>X</button>
                        </div>
                        <div>
                            <img src="../images/user1_photo2.jpg" alt="">
                            <button>X</button>
                        </div>
                        <div>
                            <img src="../images/user2_photo1.jpg" alt="">
                            <button>X</button>
                        </div>
                        <div class="available">
                            <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data">
                                <input type="file" name="fileToUpload" id="fileToUpload" style="position: absolute; top: 0; left: 0; bottom: 0; right: 0; width: 100%; height:100%;">
                            </form>
                        </div>
                        <div class="disabled"></div>
                        <div class="disabled"></div>
                    </div>

                    <div>
                        <h2>POlla</h2>
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
