<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

// Controllo sicurezza: solo l'admin può chiamare questo file
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: /403.html");
    exit();
}

$_SESSION['delete_prop_msg'] = [
    'type' => '',
    'text' => ''
];
$idProprieta = $_GET['id'] ?? '';

if (!empty($idProprieta)){
    $connessione = new DBAccess();
    $connessioneOK = $connessione -> openDBConnection();
    if ($connessioneOK) {
        $success = $connessione->deleteProprieta($idProprieta); 
        $connessione->closeDBConnection();
        
        if ($success) {
            $_SESSION['delete_prop_msg'] = [
                'type' => 'success',
                'text' => 'Proprietà eliminata con successo.'
            ];
        } else {
            $_SESSION['delete_prop_msg'] = [
                'type' => 'error',
                'text' => 'C\'è stato un problema con l\'eliminazione della proprietà.'
            ];
        }
    } else {
        header("location: /500.html");
        exit();
    }
} else {
    $_SESSION['delete_prop_msg'] = [
        'type' => 'error',
        'text' => 'Spiacenti, impossibile proseguire con l\'eliminazione: la proprietà selezionata non esiste.'
    ];
}

header("Location: /php/proprieta.php");
exit(); 
?>