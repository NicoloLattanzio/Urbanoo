<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

$paginaHTML = file_get_contents('../html/proprieta.html');

// Controllo dello stato utente (Admin o User)
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

// logica per mostrare messaggi di successo/errore dopo operazione di elimina [CONTROLLA SE VA BENE E SE SERVE LOGICA MODIFICA/AGGIUNGI] già generico volendo
/*if (isset($_GET['insertion_success_msg'])) {
    if ($_GET['insertion_success_msg'] === 'success') {
        $messaggioOperazione = 
    } elseif ($_GET['insertion_success_msg'] === 'error') {
        $messaggioOperazione = '<p class="error-message" role="alert">Si è verificato un errore durante l\'operazione.</p>';
    }
}*/

$stringaProprieta = "";
$actionMap = [
    'insert_prop_msg' => 'insert-id',
    'delete_prop_msg'=> 'delete-id',
    'change_prop_msg'=> 'change-id',
    'show_prop_msg'=> 'view-id',
];
$msg = null;
$actionId = null;

foreach ($actionMap as $sessionKey => $id) {
    if (isset($_SESSION[$sessionKey])) {
        $msg = $_SESSION[$sessionKey];
        $actionId = $id;
        unset($_SESSION[$sessionKey]);
        break; // prende solo il primo messaggio trovato
    }
}

if ($connessioneOK) {
    $filtriAttivi = array_filter($_GET, function ($value) {
        return $value !== '';
    }); //i campi 0, 1 vengono tenuti validi

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

    if (!empty($listaProprieta)) {
        if ($msg) {
            $placeholders = [
                '[action-id]' => $actionId,
                '[action-class]' => $msg['type'] === 'error'
                    ? 'error-msg display-msg'
                    : 'success-msg display-msg',
                '[action-status-msg]' => htmlspecialchars($msg['text'])
            ];
            $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
        }
        if ($isAdmin) {
            $stringaProprieta .= '<div class="admin-controls"><a href="/php/inserisci_proprieta.php" class="btn-add">Aggiungi Nuova Proprietà</a></div>';
        }

        $stringaProprieta .= '<ul class="property-list">'; 

        foreach ($listaProprieta as $proprieta) {
            $stringaProprieta .= '<li>';
            $stringaProprieta .= '<div class="property-card"><h3 class="card-title">' . $proprieta['nome'] . '</h3>';
            $stringaProprieta .= '<img src="' . $proprieta['immagine'] . '" alt="Foto di ' . $proprieta['nome'] . '" />';
            // Tutti gli utenti hanno il pulsante "Vedi"
            $stringaProprieta .= '<div class="user-actions"><a href="/php/dettagli_proprieta.php?id=' . $proprieta['id'] . '" id="view-link" class="action-button" aria-label="Vedi i dettagli di ' . $proprieta['nome'] . '">Vedi</a></div>';

            if ($isAdmin) {
    			// L'admin vede "Modifica" che va alla pagina dettagli
    			$stringaProprieta .= '<div class="user-actions"><a href="/php/modifica_proprieta.php?id=' . $proprieta['id'] . '" id="change-link" class="action-button" aria-label="Modifica i dettagli di ' . $proprieta['nome'] . '">Modifica</a></div>';
    			// L'admin vede "Elimina" che attiva uno script di cancellazione: iniziamente blocco nascosto poi attivato da JS e mostrato a schermo
    			$stringaProprieta .= '  <div class="user-actions"><a href="/php/elimina_proprieta.php?id=' . $proprieta['id'] . '" id="delete-link" class="action-button" aria-label="Elimina ' . $proprieta['nome'] . '">Elimina</a></div>
                                        <div id="delete-dialog" class="hide" role="alertdialog" aria-modal="true" aria-labelledby="delete-title" aria-describedby="delete-desc">
                                            <h2 id="delete-title">Conferma eliminazione</h2>
                                            <p id="delete-desc">Sei sicuro di voler eliminare questa proprietà?</p>
                                            <button id="confirm-delete">Elimina</button>
                                            <button id="cancel-delete">Annulla</button>
                                        </div>';	//si arrangia con js
		    }
		    $stringaProprieta .= '</div></li>';
        }
        $stringaProprieta .= '</ul>';
    } else {
        $stringaProprieta = "<p>Nessuna proprietà corrisponde alla tua ricerca</p>";
    }
} else {
    header("location: /500.html");
    exit();
    //$stringaProprieta = '<p>I sistemi sono momentaneamente fuori servizio, ci scusiamo per il disagio. Ci stiamo occupando del problema, riprova più tardi oppure contattaci attraverso <a href="/contatti.html" aria-label="pagina dei contatti">questa pagina</a></p>';
}

$paginaHTML = str_replace("[properties]", $stringaProprieta, $paginaHTML);
echo $paginaHTML;
?>