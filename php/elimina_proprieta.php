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
    user -> redirect to 403.html
    none -> redirect to 403.html
*/
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: /403.html");
    exit();
}
/*
    ID input management:
    -> no input: user asks for url/dettagli_proprieta.php                   -> 404.html
    -> empty input: user asks for url/dettagli_proprieta.php?id=            -> 404.html
    -> invalid input: user asks for url/dettagli_proprieta.php?id="uegaoef" -> 404.html
*/
$idProprieta = $_GET['id'] ?? null;
if (!ctype_digit($idProprieta) || (int)$idProprieta <= 0) {
    header('Location: /404.html');
    exit();
}
$idProprieta = (int)$idProprieta;

$connessione = new DBAccess();
$connessioneOK = $connessione -> openDBConnection();
if(!$connessioneOK){
    //DB connection error
    header("location: /500.html");
    exit();
}

/*
    DB output management:
    -> [true, $row]: query did not affect rows  -> property not existent error      1)
    -> [true, null]: query affected > 0 rows    -> property deleted successfully    2)
    -> [false, DB_ERROR]: query failed          -> 500.html                         3)
*/
$deleteResult = $connessione->deleteProperty($idProprieta); 
$connessione->closeDBConnection();

if(!$deleteResult["success"]){
    //3)
    header("location: /500.html");
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
header("Location: /php/proprieta.php");
exit(); 
?>