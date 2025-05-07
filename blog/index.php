<?php

   $db = new PDO('sqlite:blog.db');
   $query = "SELECT * FROM configuracion";
   $stmt = $db->query($query);

   $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
	
	$query = "SELECT * FROM articulos";
	$stmt = $db->query($query);
	$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$resultado['articulos'] = $articulos;
	
	include "inc/motor.php";
	
	echo renderTemplate("plantillas/blog.html", $resultado);
	
?>
