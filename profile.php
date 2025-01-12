<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Editar Perfil</h1>
    <img src="images/profile.jpg" id="edit-profile-image" alt="imagen de perfil">

    <form action="save_profile.php" method="POST" enctype="multipart/form-data">
        <!-- Nombre -->
        <label for="nom">Nombre:</label>
        <input type="text" id="nom" name="nom" required>
        <br><br>
        
        <!-- Apellidos -->
        <label for="cognoms">Apellidos:</label>
        <input type="text" id="cognoms" name="cognoms" required>
        <br><br>

        <!-- Alias -->
        <label for="alias">Alias:</label>
        <input type="text" id="alias" name="alias" required>
        <br><br>

        <!-- Fecha de nacimiento -->
        <label for="data_naixement">Fecha de nacimiento:</label>
        <input type="date" id="data_naixement" name="data_naixement" required>
        <br><br>

        <!-- Ubicación -->
        <!-- aquí va la API de Google para seleccionar la latitud y longitud -->
        <label for="ubicacio">Ubicación:</label>
        <input type="number" id="ubicacio" name="ubicacio">
        <br><br>

        <!-- Sexo -->
        <label for="sexe">Sexo:</label>
        <select id="sexe" name="sexe" required>
            <option value="masculino">Masculino</option>
            <option value="femenino">Femenino</option>
            <option value="no-binario">Otro</option>
        </select>
        <br><br>

        <!-- Orientación sexual -->
        <label for="orientacio">Orientación sexual:</label>
        <select id="orientacio" name="orientacio" required>
            <option value="heterosexual">Heterosexual</option>
            <option value="homosexual">Homosexual</option>
            <option value="bisexual">Bisexual</option>
        </select>
        <br><br>

        <!-- Fotos del perfil -->
        <a href="./image">Imágenes de perfil</a>
        <br><br>

        <!-- Botón de enviar -->
        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>
