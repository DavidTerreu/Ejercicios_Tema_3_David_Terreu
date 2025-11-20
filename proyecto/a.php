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

        try{
            $pdo->exec("
                INSERT INTO categorias (nombre, descripcion) VALUES
                ('Cítricos', 'Frutas ricas en vitamina C'),
                ('Frutas Rojas', 'Frutas dulces y pequeñas'),
                ('Tropicales', 'Frutas exóticas de clima cálido')
            ");

            $pdo->exec("
                INSERT INTO productos (nombre, categoria_id, precio, stock) VALUES
                ('Naranjas', 1, 1.70, 120),
                ('Limones', 1, 1.10, 90),
                ('Mandarinas', 1, 2.00, 70),
                ('Fresas', 2, 3.40, 40),
                ('Frambuesas', 2, 4.20, 30),
                ('Arándanos', 2, 4.10, 15),
                ('Mango', 3, 2.70, 50),
                ('Piña', 3, 3.00, 15),
                ('Papaya', 3, 2.50, 10),
                ('Maracuyá', 3, 5.00, 8)
            ");
        } catch (PDOException $e){
            echo "Error al insertar categorías: " . $e->getMessage() . "<br><br>";
        }


        //EJERCICIO 3
        //a) Obtener todos los productos ordenados por precio (menor a mayor)
        $consultaProductos = $pdo -> prepare("SELECT * FROM productos ORDER BY precio ASC");

        $consultaProductos -> execute();

        $productos = $consultaProductos -> fetchAll(PDO::FETCH_ASSOC);

        foreach ($productos as $producto) {
            echo $producto['nombre'] . " ";
        }

        echo "<br><br>";
        //b) Obtener productos de una categoría específica
        $categoria = 'Tropicales';
        $consultaCat = $pdo -> prepare("SELECT p.nombre, c.nombre AS categoria FROM productos p JOIN categorias c ON p.categoria_id = c.id WHERE c.nombre = :categoria");
        $consultaCat -> bindParam(':categoria', $categoria);
        $consultaCat -> execute();

        $productosCategoria = $consultaCat -> fetchAll(PDO::FETCH_ASSOC);

        foreach ($productosCategoria as $producto) {
            echo $producto['nombre'] . " ";
        }

        echo "<br><br>";

        //c) Obtener productos con stock menor a 20
        $stockMAX = 20;
        $consultaStock = $pdo -> prepare("SELECT nombre, stock FROM productos WHERE stock < :stockLimite");
        $consultaStock -> bindParam(':stockLimite', $stockMAX);
        $consultaStock -> execute();

        $productosMenor20 = $consultaStock -> fetchAll(PDO::FETCH_ASSOC);

        foreach ($productosMenor20 as $producto) {
            echo $producto['nombre'] . " (" . $producto['stock'] . ") ";
        }

        echo "<br><br>";

        //d) Contar cuántos productos hay en total
        $consultaCount = $pdo -> prepare("SELECT COUNT(*) AS total_productos FROM productos");
        $consultaCount -> execute();

        $totalProductos = $consultaCount -> fetch(PDO::FETCH_ASSOC);
        echo "Total de productos: " . $totalProductos['total_productos'];

        echo "<br><br>";

        //Ejercicio 4

        $consultaJoin = $pdo -> prepare("SELECT p.nombre AS producto, p.precio, c.nombre AS categoria FROM productos p INNER JOIN categorias c ON p.categoria_id = c.id ORDER BY c.nombre, p.precio");
        $consultaJoin -> execute();

        $resultadosJoin = $consultaJoin -> fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultadosJoin as $resultado) {
            echo $resultado['producto'] . " - " . $resultado['precio'] . " - " . $resultado['categoria'] . "<br>";
        }

        echo "<br><br>";

        //Ejercicio 5
        //a) Aumente el precio de todos los productos de una categoría en un 10%
        $categoriaAct = 'Cítricos';
        $aumento = 1.10;
        $limite = 0;

        $consulta = $pdo -> prepare("SELECT p.stock FROM productos p JOIN categorias c ON p.categoria_id = c.id WHERE c.nombre = :categoria");
        $consulta -> bindParam(':categoria', $categoriaAct);
        $consulta -> execute();
        $listaCat = $consulta -> fetchColumn();

        if ($listaCat >= $limite) {
            $actualizaPrecio = $pdo -> prepare("UPDATE productos p JOIN categorias c ON p.categoria_id = c.id SET p.precio = p.precio * :aumento WHERE c.nombre = :categoria");
            $actualizaPrecio -> bindParam(':aumento', $aumento);
            $actualizaPrecio -> bindParam(':categoria', $categoriaAct);
            $actualizaPrecio -> execute();

            echo "Los precios actualizados de la categoría" . $categoriaAct . " son: ";
            echo "<br>";
            $consultaActualizados = $pdo -> prepare("SELECT p.nombre, p.precio FROM productos p JOIN categorias c ON p.categoria_id = c.id WHERE c.nombre = :categoria");
            $consultaActualizados -> bindParam(':categoria', $categoriaAct);
            $consultaActualizados -> execute();
            $productosActualizados = $consultaActualizados -> fetchAll(PDO::FETCH_ASSOC);

            foreach ($productosActualizados as $producto) {
                echo $producto['nombre'] . " - " . $producto['precio'] . "<br>";
            }
        } else {
            echo "No se puede actualizar el precio de la categoría " . $categoriaAct . " porque no hay productos.";
        }

        echo "<br><br>";

        //b) Reduzca el stock de un producto específico cuando se realiza una compra
        $productoCompra = 'Fresas';
        $cantidadCompra = 5;
        $limite = 0;

        $consulta2 = $pdo -> prepare("SELECT p.stock FROM productos p JOIN categorias c ON p.categoria_id = c.id WHERE c.nombre = :categoria");
        $consulta2 -> bindParam(':categoria', $categoriaAct);
        $consulta2 -> execute();
        $listaCat2 = $consulta2 -> fetchColumn();

        if ($listaCat2 >= $limite) {
            $actualizaStock = $pdo -> prepare("UPDATE productos SET stock = stock - :cantidad WHERE nombre = :producto");
            $actualizaStock -> bindParam(':cantidad', $cantidadCompra);
            $actualizaStock -> bindParam(':producto', $productoCompra);
            $actualizaStock -> execute();

            $consultaStockActualizado = $pdo -> prepare("SELECT nombre, stock FROM productos WHERE nombre = :producto");
            $consultaStockActualizado -> bindParam(':producto', $productoCompra);
            $consultaStockActualizado -> execute();
            $productoStockActualizado = $consultaStockActualizado -> fetch(PDO::FETCH_ASSOC);

            echo "Stock actualizado de " . $productoCompra . ": " . $productoStockActualizado['stock'];
        } else {
            echo "No se puede actualizar el precio de la categoría " . $categoriaAct . " porque no hay productos.";
        }

        echo "<br><br>";

        //c) Valide que el stock no sea negativo antes de actualizar las dos anteriores operaciones
        //Hecho arriba

        //Ejercicio 6
        //Crea un script que elimine productos sin stock (stock = 0). Pero antes, implementa un soft delete añadiendo una columna "eliminado" en la tabla productos.
        //Luego, modifica tus consultas SELECT para no mostrar productos eliminados.
        //💡 Usa UPDATE en lugar de DELETE para marcar como eliminado
        try{
            $pdo->exec("ALTER TABLE productos ADD COLUMN eliminado TINYINT(1) DEFAULT 0");
            $eliminaSinStock = $pdo -> prepare("UPDATE productos SET eliminado = 1 WHERE stock = 0");
            $eliminaSinStock -> execute();

            $mostrarNoEliminados = $pdo -> prepare("SELECT nombre, stock FROM productos WHERE eliminado = 0");
            $mostrarNoEliminados -> execute();
            $productosNoEliminados = $mostrarNoEliminados -> fetchAll(PDO::FETCH_ASSOC);

            echo "Productos no eliminados:<br>";
            foreach ($productosNoEliminados as $producto) {
                echo $producto['nombre'] . " - Stock: " . $producto['stock'];
            }
        } catch (PDOException $e){
            echo "Error al añadir tablas: " . $e->getMessage() . "<br><br>";
        }

        echo "<br><br>";

        //Ejercicio 7

        try {
            $pdo->beginTransaction();
            $usuarioId = 1;
            $productoId = 4;
            $cantidad = 3;

            // Verificar stock
            $stmt = $pdo->prepare("SELECT stock, precio FROM productos WHERE id = :productoId FOR UPDATE");
            $stmt->bindParam(':productoId', $productoId);
            $stmt->execute();
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($producto['stock'] < $cantidad) {
                throw new Exception("Stock insuficiente para el producto ID $productoId");
            }

            // Crear pedido
            $total = $producto['precio'] * $cantidad;
            $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (:usuarioId, :total)");
            $stmt->bindParam(':usuarioId', $usuarioId);
            $stmt->bindParam(':total', $total);
            $stmt->execute();

            // Reducir stock
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :productoId");
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':productoId', $productoId);
            $stmt->execute();

            $pdo->commit();

            echo "Pedido creado para el usuario con ID $usuarioId. Total: $total<br><br>";

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Error al crear el pedido: " . $e->getMessage() . "<br><br>";
        }

        echo "<br><br>";

        //Ejercicio 8
        //a) Productos más vendidos (he creado una tabla detallesPedido para insertar datos y poder hacer este ejercicio)

        $pdo->exec("
                CREATE TABLE IF NOT EXISTS detallesPedido (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    pedido_id INT NOT NULL,
                    producto_id INT NOT NULL,
                    categoria_id INT NOT NULL,
                    cantidad INT NOT NULL,
                    precio_unitario DECIMAL(10, 2) NOT NULL,
                    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
                    FOREIGN KEY (producto_id) REFERENCES productos(id),
                    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
                )
            ");

        try{
            $pdo->exec("
                INSERT INTO detallesPedido (pedido_id, producto_id, categoria_id, cantidad, precio_unitario) VALUES
                (1, 4, 2, 3, 3.40),
                (1, 1, 1, 2, 1.70),
                (2, 7, 3, 1, 2.70),
                (2, 2, 1, 4, 1.10),
                (3, 5, 2, 2, 4.20)
            ");

        } catch (PDOException $e){
            echo "Error al insertar: " . $e->getMessage() . "<br><br>";
        }

        $productoMasVendido = $pdo -> prepare("SELECT producto_id, SUM(cantidad) AS total_vendido FROM detallesPedido GROUP BY producto_id ORDER BY total_vendido DESC LIMIT 1");
        $productoMasVendido -> execute();
        $masVendido = $productoMasVendido -> fetch(PDO::FETCH_ASSOC);

        echo "Producto más vendido ID: " . $masVendido['producto_id'] . ": " . $masVendido['total_vendido'];

        echo "<br><br>";

        //b) Ingresos totales por categoría en la tabla detallespedido

        $ingresosCat = $pdo -> prepare("SELECT categoria_id, SUM(cantidad * precio_unitario) AS ingresos_totales FROM detallesPedido GROUP BY categoria_id ORDER BY ingresos_totales DESC");
        $ingresosCat -> execute();
        $ingresosCategoria = $ingresosCat -> fetchAll(PDO::FETCH_ASSOC);

        foreach ($ingresosCategoria as $ingreso) {
            echo "Categoría ID: " . $ingreso['categoria_id'] . " - Ingresos: " . $ingreso['ingresos_totales'] . "<br>";
        }

        echo "<br><br>";

        //c) Productos con bajo stock (< 10 unidades)

        $bajoStock = $pdo -> prepare("SELECT nombre, stock FROM productos WHERE stock < 10 AND eliminado = 0");
        $bajoStock -> execute();
        $productosBajoStock = $bajoStock -> fetchAll(PDO::FETCH_ASSOC);

        foreach ($productosBajoStock as $producto) {
            echo "Producto: " . $producto['nombre'] . " - Stock: " . $producto['stock'] . "<br>";
        }

        echo "<br><br>";

        //d) Usuarios con más compras

        $usuariosMasCompras = $pdo -> prepare("SELECT usuario_id, COUNT(*) AS total_compras FROM pedidos GROUP BY usuario_id ORDER BY total_compras DESC LIMIT 1");
        $usuariosMasCompras -> execute();
        $usuarioMC = $usuariosMasCompras -> fetch(PDO::FETCH_ASSOC);

        echo "Usuario con más compras: " . $usuarioMC['usuario_id'] . " - Total compras: " . $usuarioMC['total_compras'] . "<br>";

        echo "<br><br>";


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
