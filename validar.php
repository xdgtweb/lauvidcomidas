<html>
    <head>
        <html lang="es">
        <style>
            table
            {
                border: black 3px solid;
            }
            
            th
            {
                border: black 3px solid;
            }
            
            td
            {
                border: black 3px solid;
            }
        </style>
    </head>
    <body>
        <?php
        session_start();

// Conexión a la base de datos
        $db_host = "localhost";
        $db_user = "root";
        $db_pass = "";
        $db_name = "lauvid1";

        $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        if (!$conn) {
            die("Conexión fallida: " . mysqli_connect_error());
        }

// Registro de usuario
        if (isset($_POST["register"])) {
            $username = mysqli_real_escape_string($conn, $_POST["username"]);
            $password = mysqli_real_escape_string($conn, $_POST["password"]);

            // Validar usuario
            if (!preg_match('/^[a-zA-Z0-9]{5,8}$/', $username)) {
                echo "El nombre de usuario debe tener de 5 a 8 caracteres alfanuméricos.";
                exit();
            }

            // Validar contraseña
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{5,10}$/', $password)) {
                echo "La contraseña debe tener de 5 a 10 caracteres y debe contener al menos una letra mayúscula, una letra minúscula y un número.";
                exit();
            }

            // Insertar usuario en la base de datos
            $sql = "INSERT INTO usuarios (username, password) VALUES ('$username', '$password')";
            if (mysqli_query($conn, $sql)) {
                echo "Usuario registrado exitosamente.";
            } else {
                echo "Error al registrar usuario: " . mysqli_error($conn);
            }
        }

// Inicio de sesión
        if (isset($_POST["login"])) {
            $username = mysqli_real_escape_string($conn, $_POST["username"]);
            $password = mysqli_real_escape_string($conn, $_POST["password"]);

            // Validar usuario y contraseña
            if (empty($username) || empty($password)) {
                echo "Ambos campos son requeridos.";
                exit();
            }

            // Verificar credenciales en la base de datos
            $sql = "SELECT * FROM usuarios WHERE username = '$username' AND password = '$password'";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) == 1) {
                $_SESSION["username"] = $username;
            } else {
                echo "Usuario o contraseña incorrectos.";
            }
        }

// Verificación de sesión
        if (!isset($_SESSION["username"])) {
            // Mostrar formulario de inicio de sesión
            ?>
            <form method="post">
                <label for="username">Usuario:</label>
                <input type="text" name="username" id="username" required>

                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>

                <button type="submit" name="login">Iniciar sesión</button>
            </form>

            <form method="post">
                <label for="username">Usuario:</label>
                <input type="text" name="username" id="username" required>


                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
                <button type="submit" name="register">Registrarse</button>
                <?php
            } else {
// Mostrar formulario de entrada de datos
                ?>
                <form method="post">
                    <label for="comida">Comida:</label>
                    <input type="text" name="comida" id="comida" required>
                    <button type="submit" name="agregar">Agregar</button>
                </form>
                <table>
                    <tr>
                        <th>Mis comidas</th>
                        <th>Comidas en común</th>
                        <th>Sus comidas</th>
                    </tr>

                    <?php
                    // Obtener comidas del usuario actual
                    $sql = "SELECT * FROM comidas WHERE username = '{$_SESSION["username"]}'";
                    $result = mysqli_query($conn, $sql);

                    $tus_comidas = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                        $tus_comidas[] = $row["comida"];
                    }

                    // Obtener comidas de otros usuarios
                    $sql = "SELECT DISTINCT username FROM comidas WHERE username != '{$_SESSION["username"]}'";
                    $result = mysqli_query($conn, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $otras_comidas = array();
                        $comidas_en_comun = array();

                        // Obtener las comidas del usuario iterado
                        $sql2 = "SELECT * FROM comidas WHERE username = '{$row["username"]}'";
                        $result2 = mysqli_query($conn, $sql2);
                        while ($row2 = mysqli_fetch_assoc($result2)) {
                            $comida_iterado = strtolower(str_replace(" ", "", $row2["comida"]));

                            // Verificar si la comida está en común
                            if (in_array($comida_iterado, $tus_comidas)) {
                                $comidas_en_comun[] = $row2["comida"];
                            } else {
                                $otras_comidas[] = $row2["comida"];
                            }
                        }

                        // Comparar comidas
                        ?>

                        <tr>
                            <td>
                                <?php
                                echo implode("<br>", $tus_comidas);
                                ?>
                            </td>
                            <td>
                                <?php
                                echo implode("<br>", $comidas_en_comun);
                                ?>
                            </td>
                            <td>
                                <?php
                                foreach ($otras_comidas as $comida) {
                                    $comida_limpia = strtolower(str_replace(" ", "", $comida));
                                    if (!in_array($comida_limpia, $comidas_en_comun)) {
                                        echo "$comida<br>";
                                    }
                                }
                                ?>
                            </td>
                        </tr>

                        <?php
                    }
                    ?>
                </table>

                <?php
// Agregar comida a la base de datos
                if (isset($_POST["agregar"])) {
                    $comida = mysqli_real_escape_string($conn, $_POST["comida"]);

                    // Validar comida
                    if (strlen($comida) < 3 || strlen($comida) > 50) {
                        echo "El nombre de la comida debe tener entre 3 y 50 caracteres.";
                        exit();
                    }

                    // Convertir la comida a minúscula y eliminar espacios en blanco
                    $comida = strtolower(str_replace(" ", "", $comida));

                    // Verificar si la comida ya existe para el usuario actual
                    $sql = "SELECT * FROM comidas WHERE username = '{$_SESSION["username"]}' AND LOWER(comida) = '$comida'";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        echo "<label style='background: orange; padding: 5px; color: black; border-radius: 10px;'>La comida ya ha sido agregada previamente.</label>";
                    } else {
// Insertar comida en la base de datos
                        $sql = "INSERT INTO comidas (username, comida) VALUES ('{$_SESSION["username"]}', '$comida')";
                        if (mysqli_query($conn, $sql)) {
                            echo "<label style='background: green; padding: 5px; color: white; border-radius: 10px;'>Comida agregada exitosamente.</label>";
                        } else {
                            echo "<label style='background: red; padding: 5px; color: black; border-radius: 10px;'>Error al agregar comida: </label>" . mysqli_error($conn);
                        }
                    }
                }
// Cerrar sesión
                ?>
                <form method="post">
                    <button type="submit" name="logout">Cerrar sesión</button>
                </form>
                <?php
                if (isset($_POST["logout"])) {
                    session_unset();
                    session_destroy();
                    header("Location: " . $_SERVER["PHP_SELF"]);
                    exit();
                }
            }

            mysqli_close($conn);
            ?>
    </body>
</html>



