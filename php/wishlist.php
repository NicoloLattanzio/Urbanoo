<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'utente') {
    header("Location: login.php");
    exit();
}

$paginaHTML = file_get_contents('../html/wishlist.html');
$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();
$lista_output = "";

if ($connessioneOK) {
    $idLoggato = $_SESSION['id_utente'];

    // Gestione rimozione se clicchi sul tasto "Rimuovi"
    if (isset($_GET['remove'])) {
        $connessione->removeFromWishlist($idLoggato, intval($_GET['remove']));
        header("Location: wishlist.php");
        exit();
    }

    $immobiliSalvati = $connessione->getWishlist($idLoggato);
    $connessione->closeDBConnection();

    if (!empty($immobiliSalvati)) {
        $lista_output = '<ul class="property-wish">';
        foreach ($immobiliSalvati as $p) {
            // Fix percorso immagine per Docker
            $img = str_replace('../img/', '/img/', $p['immagine']);
            
            $lista_output .= '<li>
                <img src="' . $img . '" alt="Anteprima ' . $p['nome'] . '">
                <div>
                    <h3>' . $p['nome'] . '</h3>
                    // commentato per ora, non serve <p>' . $p['citta'] . ' - ' . number_format($p['prezzo'], 0, ',', '.') . ' &euro;</p>
                    <a href="dettagli_proprieta.php?id=' . $p['id'] . '">Vedi Dettagli</a> | 
                    <a href="wishlist.php?remove=' . $p['id'] . '" class="remove-btn">Rimuovi dai preferiti</a>
                </div>
            </li>';
        }
        $lista_output .= '</ul>';
    } else {
        $lista_output = '<p>Non hai ancora salvato nessuna proprietà nei tuoi preferiti.</p>';
    }
} else {
    $lista_output = '<p>Si è verificato un errore di connessione.</p>';
}

echo str_replace("[wishlist]", $lista_output, $paginaHTML);
?>