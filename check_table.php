<?php
$mysqli = new mysqli('127.0.0.1','root','');
$mysqli->select_db('ca');
$result = $mysqli->query('DESCRIBE areas;');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
    }
} else {
    echo 'Error: ' . $mysqli->error . PHP_EOL;
}
$mysqli->close();
