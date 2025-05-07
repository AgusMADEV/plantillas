<?php

include "motorplantilla.php";

$datos = [
    'titulo'  => 'Agustín Morcillo Aguado',
    'subtitulo' => "Blog de prueba",
    "texto" => "Bienvenidos a mi blog en el cual voy a hablar de ...",
    "articulos" => [
    	[
    		"titulo" => "articulo 1",
    		"texto"=>"Este es el texto del articulo 1"
    	],
    	[
    		"titulo" => "articulo 2",
    		"texto"=>"Este es el texto del articulo 2"
    	],
    	[
    		"titulo" => "articulo 3",
    		"texto"=>"Este es el texto del articulo 3"
    	],
    	[
    		"titulo" => "articulo 4",
    		"texto"=>"Este es el texto del articulo 4"
    	]
    ]
];

echo renderTemplate('prueba.html', $datos);

?>
