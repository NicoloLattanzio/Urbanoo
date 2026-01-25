<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

$paginaHTML = file_get_contents('../html/proprieta.html');

/*
    User role check:
    admin -> shows admin control buttons: insert | delete | edit + show
    user -> shows user button: show
*/
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

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
    // Se ci sono filtri, usiamo la funzione specifica
    $result = $connessione->getFilteredProperty(
        $_GET['title'] ?? '',  // "??" opperatore null coalescing per gestire parametri non settati (nulli)
        $_GET['city'] ?? '',
        $_GET['type'] ?? '',
        $_GET['price_min'] ?? '',
        $_GET['price_max'] ?? '',
        $_GET['size'] ?? ''
    );

    $connessione->closeDBConnection();

    if ($result['success']) {
        if(!empty($result['content'])) {
            $listaProprieta = $result['content'];
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
                $stringaProprieta .= '<div class="admin-controls"><a href="inserisci_proprieta.php" class="btn-add">Aggiungi Nuova Proprietà</a></div>';
            }

            $stringaProprieta .= '<div class="property-grid">';

            foreach ($listaProprieta as $proprieta) {
                $stringaProprieta .= '<div class="property-card">';
                $stringaProprieta .= '<img src="' . $proprieta['immagine'] . '" alt="Foto di ' . $proprieta['nome'] . '" />';
                $stringaProprieta .= '<h3>' . $proprieta['nome'] . '</h3>';
                $stringaProprieta .= '<p class="price">Prezzo:' . $proprieta['prezzo'] . '</p>';
                $stringaProprieta .= '<div class="property-details">';
                $stringaProprieta .= '<p class="card-details">Metri Quadri:' . $proprieta['metri_quadri'] . '</p>';
                $stringaProprieta .= '<p class="card-details">Nr Locali:' . $proprieta['locali'] . '</p>';
                $stringaProprieta .= '<p class="card-details">Tipologia:' . $proprieta['tipologia'] . '</p>';
                $stringaProprieta .= '</div>';
                // Tutti gli utenti hanno il pulsante "Vedi"
                $stringaProprieta .= '<div><a class="btn-view" href="dettagli_proprieta.php?id=' . $proprieta['id'] . '" id="view-link" class="action-button" aria-label="Vedi i dettagli di ' . $proprieta['nome'] . '">Vedi</a></div>';

                if ($isAdmin) {
                    // L'admin vede "Modifica" che va alla pagina dettagli
                    $stringaProprieta .= '<div class="btn-view"><a id="btn-mod" href="modifica_proprieta.php?id=' . $proprieta['id'] . '" id="change-link" class="action-button" aria-label="Modifica i dettagli di ' . $proprieta['nome'] . '">Modifica</a></div>';
                    // L'admin vede "Elimina" che attiva uno script di cancellazione: iniziamente blocco nascosto poi attivato da JS e mostrato a schermo
                    $stringaProprieta .= '  <div class="btn-view"><a id="btn-del" href="elimina_proprieta.php?id=' . $proprieta['id'] . '" id="delete-link" class="action-button" aria-label="Elimina ' . $proprieta['nome'] . '">Elimina</a></div>
                                            <div id="delete-dialog" class="hide" role="alertdialog" aria-modal="true" aria-labelledby="delete-title" aria-describedby="delete-desc">
                                                <h2 id="delete-title">Conferma eliminazione</h2>
                                                <p id="delete-desc">Sei sicuro di voler eliminare questa proprietà?</p>
                                                <button id="confirm-delete">Elimina</button>
                                                <button id="cancel-delete">Annulla</button>
                                            </div>';	//si arrangia con js
                }
                $stringaProprieta .= '</div>';
            }
            $stringaProprieta .= '</div>';
        } else {
            $stringaProprieta = "<p>Nessuna proprietà corrisponde alla tua ricerca</p>";
        }
    } else {
        header("location: 403.php"); //errori di funzioni di query
        exit();
    }
} else {
    header("location: 500.php");
    exit();
    //$stringaProprieta = '<p>I sistemi sono momentaneamente fuori servizio, ci scusiamo per il disagio. Ci stiamo occupando del problema, riprova più tardi oppure contattaci attraverso <a href="/contatti.html" aria-label="pagina dei contatti">questa pagina</a></p>';
}

$paginaHTML = str_replace("[properties]", $stringaProprieta, $paginaHTML);
echo $paginaHTML;
exit();
?>