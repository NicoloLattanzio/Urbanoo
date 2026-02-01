<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

// Function to format the price to display
function formatPrice($price): string
{
    if (!is_numeric($price)) {
        return '';
    }

    // normalize input (int, float, decimal string)
    $price = (float)$price;

    // < 1000
    if ($price < 1000) {
        return (string)(int)$price;
    }

    // MILLIONS
    if ($price >= 1_000_000) {
        $m = $price / 1_000_000;

        // use M only if not integer
        if (floor($m) != $m) {
            $formatted = rtrim(
                rtrim(number_format($m, 2, '.', ''), '0'),
                '.'
            );
            return $formatted . 'M';
        }
    }

    // THOUSANDS
    if ($price >= 100_000) {
        $k = $price / 1_000;

        // use k if not integer OR >= 100k
        if (floor($k) != $k || $k >= 100) {
            $formatted = rtrim(
                rtrim(number_format($k, 1, '.', ''), '0'),
                '.'
            );
            return $formatted . 'k';
        }
    }
    // DEFAULT: grouped with ,
    return number_format((int)round($price), 0, '', '.');
}


//function to print html values
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

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
    'show_prop_msg'=> 'view-id'
];
$msg = null;
$actionId = null;

foreach ($actionMap as $sessionKey => $id) {
    if (!empty($_SESSION[$sessionKey]['text'])) {
        $msg = $_SESSION[$sessionKey];
        $actionId = $id;
        unset($_SESSION[$sessionKey]);
        break; // takes only the first message found
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
    $stringaProprieta = "<p>Nessuna proprietà corrisponde alla tua ricerca.</p>";
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
        $stringaProprieta .= '<img src="' . e($proprieta['immagine']) . '" alt="" />';
        $stringaProprieta .= '<h3>' . e($proprieta['nome']) . '</h3>';
        $stringaProprieta .= '<div class="property-details">';
        $stringaProprieta .= '<p class="price">Prezzo: &euro; ' . formatPrice(e($proprieta['prezzo'])) . '</p>';
        $stringaProprieta .= '<p class="card-details">Metri Quadri:' . e($proprieta['metri_quadri']) . '</p>';
        $stringaProprieta .= '<p class="card-details"><abbr title="Numero">Nr</abbr> Locali:' . e($proprieta['locali']) . '</p>';
        $stringaProprieta .= '<p class="card-details">Tipologia:' . e($proprieta['tipologia']) . '</p>';
        $stringaProprieta .= '</div>';
        // user/admin -> button: show
        $stringaProprieta .= '<div><a class="btn-view" href="dettagli_proprieta.php?id=' . e($proprieta['id']) . ' "class="action-button" aria-label="Vedi i dettagli di ' . e($proprieta['nome']) . '">Vedi</a></div>';

        if ($isAdmin) {
            // admin -> edit button: take to edit page
            $stringaProprieta .= '<div><a class="btn-mod" href="modifica_proprieta.php?id=' . e($proprieta['id']) . ' "class="action-button" aria-label="Modifica i dettagli di ' . e($proprieta['nome']) . '">Modifica</a></div>';
            // admin -> delete button: activates deletion script
            $stringaProprieta .= '  <div><a class="btn-del" href="elimina_proprieta.php?id=' . e($proprieta['id']) . ' "class="action-button" aria-label="Elimina ' . e($proprieta['nome']) . '">Elimina</a></div>';
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
        '[action-status-msg]' => $msg['text']
    ];
    $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
}

$paginaHTML = str_replace("[properties]", $stringaProprieta, $paginaHTML);
echo $paginaHTML;
exit();
?>