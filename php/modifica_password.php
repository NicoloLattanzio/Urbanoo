<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

if (!isset($_SESSION['email'])) { // l'utente deve essere loggato
    header("Location: login.php"); 
    exit();
}

$paginaHTML = file_get_contents('../html/modifica_password.html');
$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email']; 
    $old   = $_POST['old_password'];
    $new   = $_POST['new_password'];

    $connessione = new DBAccess();
    if ($connessione->openDBConnection()) {
        if ($connessione->checkOldPassword($email, $old)) {
            if ($connessione->updatePassword($email, $new)) {
                $messaggio = '<p class="success-message" role="alert">Password aggiornata con successo!</p>';
            } else {
                $messaggio = '<p class="error-message" role="alert">Errore durante l\'aggiornamento nel database.</p>';
            }
        } else {
            $messaggio = '<p class="error-message" role="alert">Email o vecchia password errati.</p>';
        }
        $connessione->closeConnection();
    } else {
        $messaggio = '<p class="error-message" role="alert">Sistemi momentaneamente fuori servizio.</p>';
    }
}


// DA VEDERE SE VA BENE QUESTO REPLACE COME SOLUZIONE PER INSERIRE IL MESSAGGIO
$paginaHTML = str_replace('<h1>Modifica Password</h1>', '<h1>Modifica Password</h1>' . $messaggio, $paginaHTML);

echo $paginaHTML;
?>