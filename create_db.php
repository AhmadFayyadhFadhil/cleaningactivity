<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'ca';
$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    echo "CONNECT_ERROR: " . $mysqli->connect_error;
    exit(1);
}
if ($mysqli->query("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;") === TRUE) {
    echo "CREATED";
} else {
    echo "ERROR: " . $mysqli->error;
}
$mysqli->close();
