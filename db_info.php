<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    echo "CONNECT_ERROR: " . $mysqli->connect_error . PHP_EOL;
    exit(1);
}
$exists = 'no';
$res = $mysqli->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'ca'");
if ($res && $res->num_rows > 0) {
    $exists = 'yes';
}
$vars = [];
$q = $mysqli->query("SHOW VARIABLES WHERE Variable_name IN ('version','version_comment','basedir','datadir','port')");
if ($q) {
    while ($row = $q->fetch_assoc()) {
        $vars[$row['Variable_name']] = $row['Value'];
    }
}
$mysqli->close();
echo "db_exists:" . $exists . PHP_EOL;
foreach ($vars as $k => $v) {
    echo $k . ": " . $v . PHP_EOL;
}
