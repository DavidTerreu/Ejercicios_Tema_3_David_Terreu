<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo PHP + MariaDB</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>🚀 Entorno PHP + MariaDB</h1>

    <?php
    // Información de PHP
    echo "<h2>📦 Versión de PHP</h2>";
    echo "<p class='info'>PHP " . phpversion() . "</p>";

    // Conexión a la base de datos
    echo "<h2>🔌 Conexión a MariaDB</h2>";

    $host = 'db';  // Nombre del servicio en docker-compose
    $dbname = 'testdb';
    $username = 'alumno';
    $password = 'alumno';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo "<p class='success'>✅ Conexión exitosa a la base de datos</p>";

        // Obtener versión de MariaDB
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        echo "<p class='info'>MariaDB versión: $version</p>";

        //EJERCICIO 1

        $pdo->exec("
                CREATE TABLE IF NOT EXISTS categorias (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    nombre VARCHAR(100) NOT NULL,
                    descripcion TEXT,
                    UNIQUE (nombre)
                )
            ");

        $pdo->exec("
                CREATE TABLE IF NOT EXISTS productos (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    nombre VARCHAR(150) NOT NULL,
                    categoria_id INT NOT NULL,
                    precio DECIMAL(10, 2) NOT NULL,
                    stock INT DEFAULT 0,
                    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
                )
            ");

        $pdo->exec("
                CREATE TABLE IF NOT EXISTS usuarios (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    nombre VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    contraseña VARCHAR(255) NOT NULL,
                    UNIQUE (email)
                )
            ");

        $pdo->exec("
                CREATE TABLE IF NOT EXISTS pedidos (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    usuario_id INT NOT NULL,
                    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
                    total DECIMAL(10, 2) NOT NULL,
                    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
                )
            ");

        //EJERCICIO 2

        $pdo->exec("
            INSERT INTO categorias (nombre, descripcion) VALUES
            ('Cítricos', 'Frutas ricas en vitamina C'),
            ('Frutas Rojas', 'Frutas dulces y pequeñas'),
            ('Tropicales', 'Frutas exóticas de clima cálido')
        "):

        $pdo->exec("
            INSERT INTO productos (nombre, categoria_id, precio, stock) VALUES
            ('Naranjas', 1, 1.70, 100),
            ('Limones', 1, 1.10, 80),
            ('Mandarinas', 1, 2.00, 60),
            ('Fresas', 2, 3.50, 40),
            ('Frambuesas', 2, 4.20, 30),
            ('Arándanos', 2, 4.00, 25),
            ('Mango', 3, 2.80, 50),
            ('Piña', 3, 3.00, 20),
            ('Papaya', 3, 2.50, 15),
            ('Maracuyá', 3, 5.00, 10)
        "):


    } catch(PDOException $e) {
        echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
        echo "<div class='info'>";
        echo "<strong>Verifica que:</strong><br>";
        echo "- Los contenedores estén corriendo: <code>docker compose -f docker-compose-alumnos.yml ps</code><br>";
        echo "- El servicio de base de datos esté disponible<br>";
        echo "- Las credenciales sean correctas";
        echo "</div>";
    }
    ?>

    <h2>🔗 Enlaces Útiles</h2>
    <div class="info">
        <p><strong>phpMyAdmin:</strong> <a href="http://localhost:8081" target="_blank">http://localhost:8081</a></p>
        <p><strong>Credenciales BD:</strong></p>
        <ul>
            <li>Usuario: <code>alumno</code></li>
            <li>Contraseña: <code>alumno</code></li>
            <li>Base de datos: <code>testdb</code></li>
        </ul>
    </div>
</div>
</body>
</html>
