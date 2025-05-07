<?php
   // Conexión y configuración de errores
   $db = new PDO('sqlite:blog.db');
   $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   // --- NUEVO: si estamos en modo insertar y recibe un POST, insertamos ---
   if (isset($_GET['insertar']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
       // 1) Leer la estructura de la tabla articulos
       $colStmt = $db->query("PRAGMA table_info('articulos')");
       $cols    = $colStmt->fetchAll(PDO::FETCH_ASSOC);
       // 2) Filtrar la columna 'id' (asumida AUTO-INCREMENT)
       $fields = array_filter($cols, fn($col) => strtolower($col['name']) !== 'id');
       $names  = array_map(fn($col) => $col['name'], $fields);
       // 3) Construir placeholders y SQL
       $phs   = array_map(fn($n) => ":$n", $names);
       $sql   = "INSERT INTO articulos (" . implode(', ', $names) . ")
                 VALUES (" . implode(', ', $phs) . ")";
       $stmt  = $db->prepare($sql);
       // 4) Bind de valores (si 'fecha' viene vacío, usamos fecha actual)
       foreach ($names as $colName) {
           $val = $_POST[$colName] ?? '';
           if ($colName === 'fecha' && trim($val) === '') {
               $val = date('Y-m-d H:i:s');
           }
           $stmt->bindValue(":$colName", $val);
       }
       $stmt->execute();
       // 5) Redirigir para evitar reenvío al refrescar
       header('Location: admin.php');
       exit;
   }
   // --- FIN BLOQUE INSERT ---

   // Código existente para listar configuración y articulos
   $query = "SELECT * FROM configuracion";
   $stmt  = $db->query($query);
   $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

   $query = "SELECT * FROM articulos";
   $stmt  = $db->query($query);
   $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $resultado['articulos'] = $articulos;

   if (isset($_GET['insertar'])) {
       $resultado['insertar'] = true;
   }

   // Obtenemos los campos para generar dinámicamente el formulario
   $query = "SELECT name AS nombredelcampo FROM pragma_table_info('articulos');";
   $stmt  = $db->query($query);
   $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $resultado['campos'] = $columnas;

   include "inc/motor.php";
   echo renderTemplate("plantillas/escritorio.html", $resultado);
?>
