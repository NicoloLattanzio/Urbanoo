<?php

$host = "localhost";
$port = "5432";
$dbname = "urbanoo";
$user = "postgres"; 
$password = "postgres"; 

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$db = pg_connect($conn_string);

if (!$db) {
    die("Errore di connessione al database.");
}

?>