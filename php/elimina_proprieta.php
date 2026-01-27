<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

$_SESSION['delete_prop_msg'] = [
    'type' => '',
    'text' => ''
];
/*
    User role check:
    admin -> can delete a property
    user -> redirect to 403.php
    none -> redirect to 403.php
*/
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: 403.php");
    exit();
}
/*
    ID input management:
    -> no input: user asks for url/dettagli_proprieta.php                   -> 404.php
    -> empty input: user asks for url/dettagli_proprieta.php?id=            -> 404.php
    -> invalid input: user asks for url/dettagli_proprieta.php?id="uegaoef" -> 404.php
*/
$idProprieta = $_GET['id'] ?? null;
if (!ctype_digit($idProprieta) || (int)$idProprieta <= 0) {
    header('Location: 404.php');
    exit();
}
$idProprieta = (int)$idProprieta;

$connessione = new DBAccess();
$connessioneOK = $connessione -> openDBConnection();
if(!$connessioneOK){
    //DB connection error
    header("location: 500.php");
    exit();
}

/*
    DB output management:
    -> [true, NOT FOUND]: query did not affect rows -> property not existent error      1)
    -> [true, null]: query affected > 0 rows        -> property deleted successfully    2)
    -> [false, DB_ERROR]: query failed              -> 500.php                          3)
*/
$deleteResult = $connessione->deleteProperty($idProprieta); 
$connessione->closeDBConnection();

if(!$deleteResult["success"]){
    //3)
    header("location: 500.php");
    exit();
}
if(!$deleteResult["content"]) {
    //2)
    $_SESSION['delete_prop_msg'] = [
        'type' => 'success',
        'text' => 'Proprietà eliminata con successo.'
    ];
} else {
    //1)
    $_SESSION['delete_prop_msg'] = [
        'type' => 'error',
        'text' => 'Spiacenti, impossibile proseguire con l\'eliminazione: la proprietà selezionata non esiste.'
    ];
}
header("Location: proprieta.php");
exit(); 
?>