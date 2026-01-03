<?php
session_start(); // Fondamentale per leggere lo stato dell'utente
require_once "dbconnection.php";
use DB\DBAccess;

//$paginaHTML = file_get_contents('..' . DIRECTORY_SEPARATOR .'php'. DIRECTORY_SEPARATOR . 'proprieta.html');
$paginaHTML = file_get_contents('../html/areariservata.html');


// Controllo dello stato utente (Admin o User)
// Assumiamo che salvate il ruolo in $_SESSION['role'] al momento del login
if(!isset($_SESSION['role'])){
    header("Location: ../403.html"); //Senno si puo reindirizzare direttamente a login
    exit();
}else{
    $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}


$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

//se non sei ne utente normale ne admin reindirizza al login.html

if ($connessioneOK) {
    $connessione->closeDBConnection();
    $paginaHTML = str_replace("[nome]", $_SESSION['name'], $paginaHTML);
    $paginaHTML = str_replace("[ruolo]", $_SESSION['role'], $paginaHTML);
    if ($isAdmin) {
        // Codice per amministratori
        $paginaHTML = str_replace("[wishlist]", "", $paginaHTML);
    } else {
        // Codice per utenti normali
        $paginaHTML = str_replace("[wishlist]", "<li><a href=\"../php/wishlist.php\" id=\"wishlist\">La mia <span lang=\"en\">wishlist</span></a></li>", $paginaHTML);
    }
    
} else {
    // Gestione errore connessione DB
    $paginaHTML = str_replace("[wishlist]", "<p>Non è stato possibile caricare la sua <span lang='en'>wishlist</span>. I sistemi sono momentaneamente fuori servizio, ci scusiamo per il disagio. Ci stiamo occupando del problema, riprova più tardi oppure contattaci a questa <span lang='en'>mail</span>: help@urbanoo.com</p>", $paginaHTML);
}
echo $paginaHTML;