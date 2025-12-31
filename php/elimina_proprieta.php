<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

// Controllo sicurezza: solo l'admin può chiamare questo file
if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin' && isset($_GET['id'])) {
    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $id = $_GET['id'];
        $successo = $db->deleteProprieta($id); 
        $db->closeDBConnection();
        
        if ($successo) {
            header("Location: proprieta.php?msg=success");
        } else {
            header("Location: proprieta.php?msg=error");
        }
        exit();
    }
}

header("Location: proprieta.php");
exit(); 
?>