<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

// Controllo sicurezza: solo l'admin può chiamare questo file
if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin' && isset($_GET['id'])) {
    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $id = $_GET['id'];
        // Qui chiamerai una funzione che scriveremo in DBAccess
        $db->deleteProprieta($id); 
        $db->closeConnection();
    }
}

// Torna automaticamente alla pagina delle proprietà
header("Location: proprieta.php");
exit(); 
?>