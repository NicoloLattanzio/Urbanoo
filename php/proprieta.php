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
if(!$connessioneOK){
    //DB connection error
    header("location: 500.php");
    exit();
}

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
    if (!empty($_SESSION[$sessionKey]['text'])) {
        $msg = $_SESSION[$sessionKey];
        $actionId = $id;
        unset($_SESSION[$sessionKey]);
        break; // prende solo il primo messaggio trovato
    }
}

/*
    Connection established successfully:
    1) no filters + no messages -> user visits for the first time the page
        in this case we hide the message div and show all properties
    2) no filters + messages -> user inserted/edited/deleted a property and is redirected here (so user = admin)
        in this case we show the message div with the appropriate message and show all properties
    3) filters + no messages -> user applied filters
        in this case we hide the message div and show all properties matching the filters
*/

$result = $connessione->getFilteredProperty(
    $_GET['title'] ?? '',  // "??" opperatore null coalescing per gestire parametri non settati (nulli)
    $_GET['city'] ?? '',
    $_GET['type'] ?? '',
    $_GET['price_min'] ?? '',
    $_GET['price_max'] ?? '',
    $_GET['size'] ?? ''
);
$connessione->closeDBConnection();
/*
    DB output management:
    -> [true, $rows]: query returned a result       
            -> properties: display them         1A)
            -> null: return a msg               1B)
    -> [false, DB_ERROR]: query failed
            -> 500.php                          2A)
*/
if(!$result['success']) {
    //2A)
    header("location: 500.php");
    exit();
}
$listaProprieta = $result['content'];

if(empty($listaProprieta)) {
    //1B)
    $stringaProprieta = "<p>Nessuna proprietà corrisponde alla tua ricerca</p>";
} else {
    //1A)
    // admin -> insert button
    if ($isAdmin) {
        $stringaProprieta .= '<div class="admin-controls"><a href="inserisci_proprieta.php" class="btn-add">Inserisci Nuova Proprietà</a></div>';
    }
    $stringaProprieta .= '<div class="property-grid">';

    foreach ($listaProprieta as $proprieta) {
        $cardClasses = ($isAdmin) ? 'property-card' : 'property-card btn-user';
        $stringaProprieta .= '<div class="' . $cardClasses . '">';
        $stringaProprieta .= '<img src="' . $proprieta['immagine'] . '" alt="" />';
        $stringaProprieta .= '<h3>' . $proprieta['nome'] . '</h3>';
        $stringaProprieta .= '<div class="property-details">';
        $stringaProprieta .= '<p class="price">Prezzo:' . $proprieta['prezzo'] . '</p>';
        $stringaProprieta .= '<p class="card-details">Metri Quadri:' . $proprieta['metri_quadri'] . '</p>';
        $stringaProprieta .= '<p class="card-details"><abbr title="Numero">Nr</abbr> Locali:' . $proprieta['locali'] . '</abbr></p>';
        $stringaProprieta .= '<p class="card-details">Tipologia:' . $proprieta['tipologia'] . '</p>';
        $stringaProprieta .= '</div>';
        // user/admin -> button: show
        $stringaProprieta .= '<div><a class="btn-view" href="dettagli_proprieta.php?id=' . $proprieta['id'] . '" id="view-link" class="action-button" aria-label="Vedi i dettagli di ' . $proprieta['nome'] . '">Vedi</a></div>';

        if ($isAdmin) {
            // admin -> edit button: take to edit page
            $stringaProprieta .= '<div><a class="btn-mod" href="modifica_proprieta.php?id=' . $proprieta['id'] . '" id="change-link" class="action-button" aria-label="Modifica i dettagli di ' . $proprieta['nome'] . '">Modifica</a></div>';
            // admin -> delete button: activates deletion script
            $stringaProprieta .= '  <div><a class="btn-del" href="elimina_proprieta.php?id=' . $proprieta['id'] . '" id="delete-link" class="action-button" aria-label="Elimina ' . $proprieta['nome'] . '">Elimina</a></div>';
        }
        $stringaProprieta .= '</div>';
    }
    $stringaProprieta .= '</div>';
}

//1), 3)
if (!$msg) {
    // $_SESSION[$sessionKey] has all values null
    $placeholders = [
        '[action-id]' => 'hidden-id',
        '[action-class]' => 'none',
        '[action-status-msg]' => '' // Empty message for hidden div
    ];
    $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
} else {
    //2)
    $placeholders = [
        '[action-id]' => $actionId,
        '[action-class]' => $msg['type'] === 'error'
            ? 'error-msg display-msg'
            : 'success-msg display-msg',
        '[action-status-msg]' => htmlspecialchars($msg['text'])
    ];
    $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
}

$paginaHTML = str_replace("[properties]", $stringaProprieta, $paginaHTML);
echo $paginaHTML;
exit();
?>