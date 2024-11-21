<?php

$servername = "agni.iad1-mysql-e2-9b.dreamhost.com";
$username = "apipython";
$password = "apipython22";
$dbname = "apipython";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Configuración de los clientes SOAP
$clients = [
    [
        'ws_pid' => 362,
        'ws_passwd' => 'CODIGO362',
        'column' => 'lista1'
    ],
    [
        'ws_pid' => 707,
        'ws_passwd' => 'CODIGO707',
        'column' => 'lista2'
    ],
    [
        'ws_pid' => 783,
        'ws_passwd' => 'CODIGO53',
        'column' => 'lista3'
    ],
    [
        'ws_pid' => 219,
        'ws_passwd' => 'CODE219',
        'column' => 'lista4'
    ],
    [
        'ws_pid' => 1943,
        'ws_passwd' => 'CODE1943',
        'column' => 'lista5'
    ]
];

$siretUrl = 'eurocompcr.com';

foreach ($clients as $clientConfig) {
    $ws_pid = $clientConfig['ws_pid'];
    $ws_passwd = $clientConfig['ws_passwd'];
    $column = $clientConfig['column'];
    $provId = 1;

    $params = [
        'ws_pid' => $ws_pid,
        'ws_passwd' => $ws_passwd,
        'bid' => 0
    ];

    $client = new SoapClient("https://" . $siretUrl . ":443/webservice.php?wsdl");

    try {
        $response = $client->__soapCall('wsc_request_bodega_all_items', $params);
        $soapProducts = $response['data'];
    } catch (\SoapFault $e) {
        echo "Error al consultar cliente $ws_pid: " . $e->getMessage() . PHP_EOL;
        continue;
    }

    foreach ($soapProducts as $soapProduct) {
        $sku = $soapProduct->codigo;
        $precio = $soapProduct->precio;

        // Actualiza o inserta los datos en la base de datos
        $stmt = $conn->prepare("
            INSERT INTO priceList (sku, $column, provId)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE $column = VALUES($column)
        ");
        $stmt->bind_param("sdi", $sku, $precio, $provId);
        $stmt->execute();
        $stmt->close();
    }

    echo "Cliente $ws_pid procesado correctamente." . PHP_EOL;
}

$conn->close();
