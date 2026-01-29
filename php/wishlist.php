<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

//function to print html values
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$_SESSION['permission'] = [
    'type' => '',
    'text' => ''
];
/*
    User role check:
    admin -> cannot access wishlist -> redirect to areariservata.php with info message
    user -> show wishlist or perform actions add/remove
    none -> redirect to 403.php
*/
if (!isset($_SESSION['role'])) {
    header("Location: 403.php");
    exit();
} else if($_SESSION['role'] === 'admin'){
    $_SESSION['permission'] = [
        'type' => 'info',
        'text' => '<p>Sei un amministratore, non puoi accedere alla tua <span lang="en">wishlist</span>.</p>'
    ];
    header("Location: areariservata.php");
    exit();
}

$_SESSION['insert_to_wishlist'] = [
    'type' => '',
    'text' => ''
];
$paginaHTML = file_get_contents('../html/wishlist.html');
$displayWishlist = "";
$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();
$placeholders = [];

if (!$connessioneOK){
    //DB error
    header('location: 500.php');
    exit();
}

$userId = $_SESSION['user_id'];
/*
    Display remove_from_wishlist message if set
*/
if (empty($_SESSION['remove_from_wishlist']['text'])) {
    unset($_SESSION['remove_from_wishlist']);
    $placeholders = [
        '[action-id]' => 'hidden-id',
        '[action-class]' => 'none',
        '[action-status-msg]' => '' // Empty message for hidden div
    ];
    $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
} else {
    $msg = $_SESSION['remove_from_wishlist'];
    unset($_SESSION['remove_from_wishlist']);
    $placeholders = [
        '[action-id]' => 'remove-wishlist-id',
        '[action-class]' => $msg['type'] === 'error'
            ? 'error-msg display-msg'
            : 'success-msg display-msg',
        '[action-status-msg]' => $msg['text']
    ];
    $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
}
/*
    Handle add/remove actions from wishlist:
        -> add: action from dettagli_proprieta.php -> redirect back to dettagli_proprieta.php with msg
        -> remove: action from wishlist.php -> redirect back to wishlist.php with msg
*/
$propertyIdToAdd = $_GET['add'] ?? '';
$propertyIdToRemove = $_GET['remove'] ?? '';
if ($propertyIdToAdd) {
    // Validate property ID format: ID must be a positive integer
    if(preg_match('/^[1-9][0-9]*$/', $propertyIdToAdd)){
        $propertyIdToAdd = (int)$propertyIdToAdd;
        /*
            DB output management:
            -> [true, null]: affected rows > 0                          -> display success message in dettagli_proprieta.php    1)
            -> [false, INSERT_ERROR]: affected rows = 0                 -> display error message in dettagli_proprieta.php      2)
            -> [false, ALREADY_EXISTS]: property already in wishlist    -> display error message in dettagli_proprieta.php      3)
            -> [false, DB_ERROR]: query failed                          -> 500.php                                              4)
        */
        $insertResult = $connessione->insertToWishlist($userId, $propertyIdToAdd);
        if($insertResult['success']){
            //1)
            $_SESSION['insert_to_wishlist'] = [
                'type' => 'success',
                'text' => '<p>Proprietà aggiunta alla <span lang="en">wishlist</span> con successo.</p>'
            ];
        } else if($insertResult['content'] === 'INSERT_ERROR'){
            //2)
            $_SESSION['insert_to_wishlist'] = [
                'type' => 'error',
                'text' => '<p>Si è verificato un errore durante l\'aggiunta della proprietà alla <span lang="en">wishlist</span>. Riprova più tardi.</p>
                            <p>Se il problema persiste, contatta l\'assistenza a questo indirizzo: <a href="mailto:info@urbanoo.it">help@urbanoo.it</a></p>'
            ];
        } else if($insertResult['content'] === 'ALREADY_EXISTS'){
            //3)
            $_SESSION['insert_to_wishlist'] = [
                'type' => 'error',
                'text' => '<p>La proprietà è già presente nella tua <span lang="en">wishlist</span>.</p>'
            ];
        } else if($insertResult['content'] === 'DB_ERROR'){
            //4)
            header("Location: 500.php");
            exit();
        }
        header("Location: dettagli_proprieta.php?id=" . $propertyIdToAdd);
        exit();
    } else {
        // Invalid property ID format
        header("Location: 404.php");
        exit();
    }
}
if ($propertyIdToRemove) {
    // Validate property ID format: ID must be a positive integer
    if(preg_match('/^[1-9][0-9]*$/', $propertyIdToRemove)){
        $propertyIdToRemove = (int)$propertyIdToRemove;
        /*
            DB output management:
            -> [true, null]: affected rows > 0          -> display success message in wishlist.php  1)
            -> [false, NOT_FOUND]: affected rows = 0    -> display error message in wishlist.php    2)
            -> [false, DB_ERROR]: query failed          -> 500.php                                  3)
        */
        $removeResult = $connessione->removeFromWishlist($userId, $propertyIdToRemove);
        if($removeResult['success']){
            //1)
            $_SESSION['remove_from_wishlist'] = [
                'type' => 'success',
                'text' => '<p>Proprietà rimossa dalla <span lang="en">wishlist</span> con successo.</p>'
            ];
        } else if($removeResult['content'] === 'NOT_FOUND'){
            //2)
            $_SESSION['remove_from_wishlist'] = [
                'type' => 'error',
                'text' => '<p>Si è verificato un errore durante la rimozione della proprietà dalla <span lang="en">wishlist</span>. Riprova più tardi.</p>
                            <p>Se il problema persiste, contatta l\'assistenza a questo indirizzo: <a href="mailto:info@urbanoo.it">help@urbanoo.it</a></p>'
            ];
        } else if($removeResult['content'] === 'DB_ERROR'){
            //3)
            header("Location: 500.php");
            exit();
        }
        header("Location: wishlist.php");
        exit();
    } else {
        // Invalid property ID format
        header("Location: 404.php");
        exit();
    }
}

// Show wishlist
/*
    DB output management:
    -> [true, $rows]: query successful
        -> $rows not empty: wishlist items found    -> display wishlist items       1)
        -> $rows empty: no wishlist items           -> display "no items" message   2)
    -> [false, DB_ERROR]: query failed              -> 500.php                      3)
*/
$wishlistResult = $connessione->getWishlist($userId);
$connessione->closeDBConnection();

if(!$wishlistResult["success"]){
    //3)
    header("Location: 500.php");
    exit();
}
$wishlist = $wishlistResult["content"];
if (empty($wishlist)){
    //2)
    $displayWishlist = '<p>Non hai ancora salvato nessuna proprietà nella tua <span lang="en">wishlist</span>.</p>';
    $paginaHTML = str_replace("[wishlist]", $displayWishlist, $paginaHTML);
    echo $paginaHTML;
    exit();
} 
$displayWishlist = '<div class="property-wishlist"><ul class="property-wishlist">';
    $displayWishlist .= '<div class="property-grid">';

foreach ($wishlist as $item) {
    //Uso le stesso metodo usato per mostrare le proprieta nella pagina proprieta.php, senza lista ma con grid quindi.
    $displayWishlist .= '<div class="property-card btn-wishlist">';

    $displayWishlist .= '<img src="' . e($item['immagine']) . '" alt="" />';
    $displayWishlist .= '<h3>' . e($item['nome']) . '</h3>';

    //Volendo si puo togliere la parte dei dettagli
    $displayWishlist .= '<div class="property-details">';
    $displayWishlist .= '<p class="price">Prezzo: ' . e($item['prezzo']) . '</p>';
    $displayWishlist .= '<p class="card-details">Metri Quadri: ' . e($item['metri_quadri']) . '</p>';
    $displayWishlist .= '<p class="card-details"><abbr title="Numero">Nr</abbr> Locali: ' . e($item['locali']) . '</abbr></p>';
    $displayWishlist .= '<p class="card-details">Tipologia: ' . e($item['tipologia']) . '</p>';
    $displayWishlist .= '</div>';

    // Pulsante view
    $displayWishlist .= '<div><a class="btn-view" href="dettagli_proprieta.php?id=' . e($item['id']) . '" class="action-button" aria-label="Vedi i dettagli di ' . e($item['nome']) . '">Vedi</a></div>';

    // Pulsante del
    $displayWishlist .= '<div><a class="btn-del" href="wishlist.php?remove=' . e($item['id']) . '" class="action-button" aria-label="Rimuovi ' . e($item['nome']) . ' dalla wishlist">Rimuovi</a></div>';
    $displayWishlist .= '</div>';
}

$paginaHTML = str_replace("[wishlist]", $displayWishlist, $paginaHTML);
echo $paginaHTML;
exit();
?>