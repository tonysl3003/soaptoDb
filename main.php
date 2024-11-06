<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios
include 'conn.php';
include 'function.php';

// Procesar datos recibidos del servicio SOAP
foreach ($soapProducts as $item) {
    // Crear o actualizar familias
    upsertFamily($conn, $item->familia_id, $item->familia);

    // Crear o actualizar productos
    upsertProduct($conn, $item->codigo, $item->descripcion, $item->familia_id, $item->marca, $item->stock, $item->image_url);
}

// Cerrar conexión a la base de datos
$conn->close();
