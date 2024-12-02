<?php

 //Configuración de conexión a la base de datos
$servername = "localhost";
$username = "aqexsbali_diviaNew";
$password = "r_GYguYQ+g_E";
$dbname = "aqexsbali_diviaNew";

//$servername = "agni.iad1-mysql-e2-9b.dreamhost.com";
//$username = "apipython";
//$password = "apipython22";
//$dbname = "apipython";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$siretUrl = 'eurocompcr.com';
$ws_pid = 707;
$ws_passwd = 'CODIGO707';
$consulta = 'wsc_request_bodega_all_items';

$params = array(
    'ws_pid' => $ws_pid,
    'ws_passwd' => $ws_passwd,
    'bid' => 0
);

$client = new SoapClient("https://" . $siretUrl . ":443/webservice.php?wsdl");

try {
    $response = $client->__soapCall($consulta, $params);
    $soapProducts = $response['data'];
} catch (\SoapFault $e) {
    echo "==> Error: " . $e->getMessage() . PHP_EOL;
    exit();
}