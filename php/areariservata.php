<?php
session_start(); // Fondamentale per leggere lo stato dell'utente
require_once "dbconnection.php";
use DB\DBAccess;

//$paginaHTML = file_get_contents('..' . DIRECTORY_SEPARATOR .'php'. DIRECTORY_SEPARATOR . 'proprieta.html');
$paginaHTML = file_get_contents('../html/areariservata.html');

// Controllo dello stato utente (Admin o User)
// Assumiamo che salvate il ruolo in $_SESSION['ruolo'] al momento del login
$isAdmin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();


if ($connessioneOK) {
    $connessione->closeConnection();
    $paginaHTML = str_replace("[nome]", $_SESSION['nome'], $paginaHTML);
    $paginaHTML = str_replace("[ruolo]", $_SESSION['ruolo'], $paginaHTML);
    if ($isAdmin) {
        // Codice per amministratori
        $paginaHTML = str_replace("[wishlist]", "", $paginaHTML);
    } else {
        // Codice per utenti normali
        $paginaHTML = str_replace("[wishlist]", "<li><a href=\"../php/wishlist.php\" id=\"wishlist\">La mia wishlist</a></li>", $paginaHTML);
    }
    
} else {
    // Gestione errore connessione DB
    $paginaHTML = str_replace("[wishlist]", "<p>I sistemi sono momentaneamente fuori servizio, ci scusiamo per il disagio. Ci stiamo occupando del problema, riprova più tardi oppure contattaci a questa mail: help@urbanoo.com</p>", $paginaHTML);
}
echo $paginaHTML;