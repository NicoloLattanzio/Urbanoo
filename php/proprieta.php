<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

$paginaHTML = file_get_contents('../html/proprieta.html');

// Controllo dello stato utente (Admin o User)
$isAdmin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

$stringaProprieta = "";
$messaggioOperazione = ""; 

// logica per mostrare messaggi di successo/errore dopo operazione di elimina [CONTROLLA SE VA BENE E SE SERVE LOGICA MODIFICA/AGGIUNGI] già generico volendo
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'success') {
        $messaggioOperazione = '<p class="success-message" role="alert">Operazione completata con successo.</p>';
    } elseif ($_GET['msg'] === 'error') {
        $messaggioOperazione = '<p class="error-message" role="alert">Si è verificato un errore durante l\'operazione.</p>';
    }
}

if ($connessioneOK) {
    $filtriAttivi = array_filter($_GET); 

    if (!empty($filtriAttivi)) {
        // Se ci sono filtri, usiamo la funzione specifica
        $listaProprieta = $connessione->getFilteredProprieta(
            $_GET['title'] ?? '',  // "??" opperatore null coalescing per gestire parametri non settati (nulli)
            $_GET['city'] ?? '',
            $_GET['type'] ?? '',
            $_GET['price_min'] ?? '',
            $_GET['price_max'] ?? '',
            $_GET['size'] ?? ''
        );
    } else {
        $listaProprieta = $connessione->getListProprieta();
    }

    $connessione->closeDBConnection();

    $stringaProprieta .= $messaggioOperazione;  // CONTROLLA SE VA BENE QUI

    if (!empty($listaProprieta)) {
        if ($isAdmin) {
            $stringaProprieta .= '<div class="admin-controls"><a href="../php/inserisci_proprieta.php" class="btn-add">➕ Aggiungi Nuova Proprietà</a></div>';
        }

        $stringaProprieta .= '<ul class="property-list">'; 

        foreach ($listaProprieta as $proprieta) {
            $stringaProprieta .= '<li>';
            $stringaProprieta .= '<h3>' . $proprieta['nome'] . '</h3>';
            $stringaProprieta .= '<img src="' . $proprieta['immagine'] . '" alt="Foto di ' . $proprieta['nome'] . '" />';
            // Tutti gli utenti hanno il pulsante "Vedi"
            $stringaProprieta .= '<a href="dettagli_proprieta.php?id=' . $proprieta['id'] . '" aria-label="Vedi i dettagli di ' . $proprieta['nome'] . '">Vedi</a>';

            if ($isAdmin) {
    			// L'admin vede "Modifica" che va alla pagina dettagli
    			$stringaProprieta .= '<a href="modifica_proprieta.php?id=' . $proprieta['id'] . '" aria-label="Modifica i dettagli di ' . $proprieta['nome'] . '">Modifica</a>';
    			// L'admin vede "Elimina" che attiva uno script di cancellazione  [CONTROLLA CHE SI FACCIA COSI, CONFIRM JAVASCRIPT NON NAVIGABILE SCREEN READER??]
    			$stringaProprieta .= '<a href="elimina_proprieta.php?id=' . $proprieta['id'] . '" onclick="return confirm(\'Sei sicuro di voler eliminare questa proprietà?\')" aria-label="Elimina ' . $proprieta['nome'] . '">Elimina</a>';	
		    }
		    $stringaProprieta .= '</li>';
        }
        $stringaProprieta .= '</ul>';
    } else {
        $stringaProprieta = "<p>Nessuna proprietà trovata nel database.</p>";
    }
} else {
    $stringaProprieta = '<p>I sistemi sono momentaneamente fuori servizio, ci scusiamo per il disagio. Ci stiamo occupando del problema, riprova più tardi oppure contattaci attraverso <a href="index.html" aria-label="pagina dei contatti">questa pagina</a></p>';
}

$paginaHTML = str_replace("[properties]", $stringaProprieta, $paginaHTML);
echo $paginaHTML;
?>