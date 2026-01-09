<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

if (!isset($_SESSION['role'])) {
    header("Location: 403.php");
    exit();
}elseif($_SESSION['role'] === 'admin'){
    $_SESSION['admin_wishlist'] = 'Sei l\'admin non possiedi la tua wishlist';
    header("Location: proprieta.php");
    exit();
}

$paginaHTML = file_get_contents('../html/wishlist.html');
$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();
$lista_output = "";

if ($connessioneOK) {
    $idLoggato = $_SESSION['user_id'];

    // Gestione rimozione se clicchi sul tasto "Rimuovi"
    if (isset($_GET['remove'])) {
        $connessione->removeFromWishlist($idLoggato, intval($_GET['remove']));
        header("Location: wishlist.php");
        exit();
    }
    if (isset($_GET['add'])) {
        $connessione->insertToWishlist($idLoggato, intval($_GET['add']));
        header("Location: wishlist.php");
        exit();
    }

    $immobiliSalvati = $connessione->getWishlist($idLoggato);
    $connessione->closeDBConnection();

    if (!empty($immobiliSalvati)) {
        $lista_output = '<ul class="property-wishlist">';
        foreach ($immobiliSalvati as $p) {
            // Fix percorso immagine per Docker
            $img = str_replace('../img/', '/img/', $p['immagine']);
            // commentato per ora, non serve <p>' . $p['citta'] . ' - ' . number_format($p['prezzo'], 0, ',', '.') . ' &euro;</p> dopo <h3>' . $p['nome'] . '</h3>
            $lista_output .= '<li>
                <div class="property-item">
                    <h3>' . $p['nome'] . '</h3>
                    <img src="' . $img . '" alt="Anteprima ' . $p['nome'] . '">
                    <div class="user-actions">
                        <a href="dettagli_proprieta.php?id=' . $p['id'] . '" id="view-link" class="action-button">Vedi Dettagli</a>
                    </div>
                    <div class="user-actions">
                        <a href="wishlist.php?remove=' . $p['id'] . '" id="delete-link" class="action-button">Rimuovi dai preferiti</a>
                    </div>
                </div>
            </li>';
        }
        $lista_output .= '</ul>';
    } else {
        $lista_output = '<p>Non hai ancora salvato nessuna proprietà nei tuoi preferiti.</p>';
    }
} else {
    header('location: 500.php');
    exit();
}

$paginaHTML = str_replace("[wishlist]", $lista_output, $paginaHTML);
echo $paginaHTML;
exit();
?>