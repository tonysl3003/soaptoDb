<?php

function upsertFamily($conn, $familyId, $description) {
    $stmt = $conn->prepare("SELECT descrip FROM familys WHERE id = ? AND proveedor = 1");
    $stmt->bind_param("i", $familyId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['descrip'] !== $description) {
            $updateStmt = $conn->prepare("UPDATE familys SET descrip = ? WHERE id = ? AND proveedor = 1");
            $updateStmt->bind_param("si", $description, $familyId);
            $updateStmt->execute();
            echo "Actualización de familia: ID $familyId, Descripción cambiada a '$description'.<br>";
            $updateStmt->close();
        } else {
            echo "Sin cambios en la familia: ID $familyId, Descripción ya es '$description'.<br>";
        }
    } else {
        $insertStmt = $conn->prepare("INSERT INTO familys (id, descrip, proveedor) VALUES (?, ?, 1)");
        $insertStmt->bind_param("is", $familyId, $description);
        $insertStmt->execute();
        echo "Nueva familia insertada: ID $familyId, Descripción '$description'.<br>";
        $insertStmt->close();
    }
    $stmt->close();
}

function getOrCreateBrandId($conn, $brandName) {
    $stmt = $conn->prepare("SELECT id FROM brands WHERE descrip = ? AND prov = 1");
    $stmt->bind_param("s", $brandName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Marca existente: ID {$row['id']}, Descripción '$brandName'.<br>";
        return $row['id'];
    } else {
        $insertStmt = $conn->prepare("INSERT INTO brands (descrip, prov) VALUES (?, 1)");
        $insertStmt->bind_param("s", $brandName);
        $insertStmt->execute();
        echo "Nueva marca insertada: Descripción '$brandName', ID generado {$conn->insert_id}.<br>";
        $insertStmt->close();
        return $conn->insert_id;
    }
}

function upsertProduct($conn, $codigo, $description, $familyId, $brandName, $stock, $imageUrl) {
    $brandId = getOrCreateBrandId($conn, $brandName);

    $stock = intval($stock);

    $stmt = $conn->prepare("SELECT descrip, marcaId, familiaId, stock, urlImage FROM products WHERE codigo = ? AND proveedor = 1");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $updates = [];

        // Verificar cada campo y registrar las diferencias
        if ($row['descrip'] !== $description) {
            $updates[] = "Descripción cambiada de '{$row['descrip']}' a '$description'";
        }
        if ($row['marcaId'] !== $brandId) {
            $updates[] = "Marca ID cambiada de '{$row['marcaId']}' a '$brandId'";
        }
        if ($row['familiaId'] !== $familyId) {
            $updates[] = "Familia ID cambiada de '{$row['familiaId']}' a '$familyId'";
        }
        if ($row['stock'] !== $stock) {
            $updates[] = "Stock cambiado de '{$row['stock']}' a '$stock'";
        }
        if ($row['urlImage'] !== $imageUrl) {
            $updates[] = "URL de imagen cambiada de '{$row['urlImage']}' a '$imageUrl'";
        }

        // Si hay actualizaciones, realizar el UPDATE y mostrar los cambios
        if (!empty($updates)) {
            $updateStmt = $conn->prepare("UPDATE products SET descrip = ?, marcaId = ?, familiaId = ?, stock = ?, urlImage = ? WHERE codigo = ? AND proveedor = 1");
            $updateStmt->bind_param("siisss", $description, $brandId, $familyId, $stock, $imageUrl, $codigo);
            $updateStmt->execute();
            echo "Actualización de producto: Código $codigo.<br>";
            foreach ($updates as $update) {
                echo "$update<br>";
            }
            $updateStmt->close();
        } else {
            echo "Sin cambios en el producto: Código $codigo, ya está actualizado.<br>";
        }
    } else {
        $insertStmt = $conn->prepare("INSERT INTO products (codigo, descrip, marcaId, familiaId, stock, urlImage, proveedor) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $insertStmt->bind_param("ssiiss", $codigo, $description, $brandId, $familyId, $stock, $imageUrl);
        $insertStmt->execute();
        echo "Nuevo producto insertado: Código $codigo, Descripción '$description', Marca ID $brandId, Familia ID $familyId, Stock $stock, URL Imagen '$imageUrl'.<br>";
        $insertStmt->close();
    }
    $stmt->close();
}
