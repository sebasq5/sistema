<?php
require '../../includes/db_connect.php';

$query = "SELECT * FROM peces";
$result = $conn->query($query);

$peces = [];
while ($row = $result->fetch_assoc()) {
    $peces[] = $row;
}

echo json_encode($peces);
$conn->close();
?>
