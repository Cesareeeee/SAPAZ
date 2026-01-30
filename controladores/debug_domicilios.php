<?php
require_once '../includes/conexion.php';
$res = $conn->query("DESCRIBE domicilios");
if($res) {
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
}
?>
