<?php
require_once __DIR__ . '/../includes/conexion.php';

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM `usuarios_servicio` LIKE 'nombre_anterior'");
if ($result->num_rows == 0) {
    // Add column
    $sql = "ALTER TABLE `usuarios_servicio` ADD COLUMN `nombre_anterior` VARCHAR(150) NULL AFTER `nombre`";
    if ($conn->query($sql) === TRUE) {
        echo "Column nombre_anterior added successfully";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column nombre_anterior already exists";
}
$conn->close();
?>
