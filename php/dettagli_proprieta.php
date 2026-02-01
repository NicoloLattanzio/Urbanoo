<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

$paginaHTML = file_get_contents('../html/dettagli_proprieta.html');

// Function to format prices to display
function formatWithCommas($number): string
{
    if (!is_numeric($number)) {
        return '';
    }

    return number_format((int)$number, 0, '', '.');
}

//function to print html values
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();
$_SESSION['show_prop_msg'] = [
    'type' => '',
    'text' => ''
];


if(!$connessioneOK){
    //DB connection error
    header("location: 500.php");
    exit();
}

/*
    ID input management:
    -> no input: user asks for url/dettagli_proprieta.php                   -> 404.php
    -> empty input: user asks for url/dettagli_proprieta.php?id=            -> 404.php
    -> invalid input: user asks for url/dettagli_proprieta.php?id="uegaoef" -> 404.php
*/
$idProprieta = $_GET['id'] ?? null;
if ($idProprieta && (!ctype_digit($idProprieta) || (int)$idProprieta <= 0)) {
    header('Location: 404.php');
    exit();
}
$idProprieta = (int)$idProprieta;


/*
    DB output management:
    -> [true, $row]: query returned a result        -> show the selected property   1)
    -> [true, null]: query returned an empty result -> property not existent error  2)
    -> [false, DB_ERROR]: query failed              -> 500.php                      3)
*/
$showResult = $connessione->showPropertyDetails($idProprieta);
if(!$showResult["success"]){
    //3)
    header("location: 500.php");
    exit();
}

$proprieta = $showResult["content"];
if ($proprieta) {
    //1)
    $disp = $proprieta['disponibilita'] ? 'Disponibile' : 'Non disponibile';
    /*
        DB output management:
        -> [true, $images]: query returned a result         -> extract all images                   A)
        -> [true, []]: query returned an empty result       -> property does not have other images  B)
        -> [false, DB_ERROR]: query failed                  -> 500.php                              C)
    */
    $risultatoImmagini = $connessione->getPropertyImages($idProprieta);
    $connessione->closeDBConnection();
    if (!$risultatoImmagini['success']) {
        //C)
        header("location: 500.php");
        exit();
    }
    //A)
    $immaginiExtra = $risultatoImmagini['content'];
    if ($immaginiExtra) {
        $gallery = "<div class=\"gallery\"><img id=\"main-image\" src=\"".e($proprieta['immagine'])."\" alt=\"\" class=\"main-img\" tabindex=\"0\"> <h2>[nome]</h2>
                    <div class=\"thumbnails\">";
        $gallery .= "<img src=\"".e($proprieta['immagine'])."\" alt=\"\" class=\"thumb\" tabindex=\"0\">"; // main image
        foreach ($immaginiExtra as $img):
            $gallery .= "<img src=\"".e($img)."\" alt=\"\" class=\"thumb\" tabindex=\"0\">";
        endforeach;
        $gallery .= "</div></div>";
        $dettagli_proprieta = str_replace(
        ["[galleria]", "[nome]", "[descrizione]", "[prezzo]", "[indirizzo]", "[citta]", "[tipologia]", "[metri_quadri]", "[locali]", "[disponibilita]"],
        [$gallery, e($proprieta['nome']), e($proprieta['descrizione']), formatWithCommas(e($proprieta['prezzo'])), e($proprieta['indirizzo']), e($proprieta['citta']), e($proprieta['tipologia']), e($proprieta['metri_quadri']), e($proprieta['locali']), $disp],
        "<div class = 'prop-details'>
                    [galleria]
                    <div class = 'prop-info'>
                        <dl>
                            <dt>Descrizione:</dt>
                            <dd>[descrizione]</dd>
                            <dt>Prezzo:</dt>
                            <dd>[prezzo] &euro;</dd>
                            <dt>Indirizzo:</dt>
                            <dd>[indirizzo], [citta]</dd>
                            <dt>Tipologia:</dt>
                            <dd>[tipologia]</dd>
                            <dt>Metri Quadri:</dt>
                            <dd>[metri_quadri] m&sup2;</dd>
                            <dt>Locali:</dt>
                            <dd>[locali]</dd>
                            <dt>Disponibilità:</dt>
                            <dd>[disponibilita]</dd>
                        </dl>
                    </div>"
        );
    } else {
        //B)
        $dettagli_proprieta = str_replace(
            ["[immagine]", "[nome]", "[descrizione]", "[prezzo]", "[indirizzo]", "[citta]", "[tipologia]", "[metri_quadri]", "[locali]", "[disponibilita]"],
            [e($proprieta['immagine']), e($proprieta['nome']), e($proprieta['descrizione']), formatWithCommas(e($proprieta['prezzo'])), e($proprieta['indirizzo']), e($proprieta['citta']), e($proprieta['tipologia']), e($proprieta['metri_quadri']), e($proprieta['locali']), $disp],
            "<div class = 'prop-details'>
                <div class = 'prop-cover'>
                    <img src='[immagine]'>
                    <h2>[nome]</h2>  
                </div>
                <div class = 'prop-info'>
                    <dl>
                        <dt>Descrizione:</dt>
                        <dd>[descrizione]</dd>
                        <dt>Prezzo:</dt>
                        <dd>[prezzo] &euro;</dd>
                        <dt>Indirizzo:</dt>
                        <dd>[indirizzo], [citta]</dd>
                        <dt>Tipologia:</dt>
                        <dd>[tipologia]</dd>
                        <dt>Metri Quadri:</dt>
                        <dd>[metri_quadri] m&sup2;</dd>
                        <dt>Locali:</dt>
                        <dd>[locali]</dd>
                        <dt>Disponibilità:</dt>
                        <dd>[disponibilita]</dd>
                    </dl>
                </div>"
        );
    }
    // if user is logged in as 'user', show "add to wishlist" button
    if(isset($_SESSION['role']) && $_SESSION['role'] === 'utente'){
        $dettagli_proprieta .= "<a href='wishlist.php?add=".e($idProprieta)."' class='add-wishlist-btn'>Aggiungi alla <span lang='en'>wishlist</span></a>
                                </div>";
    // if not logged in or logged in as 'admin', do not show the button
    }else{
        $dettagli_proprieta .= "</div>";
    }

    /*
        Display insert_to_wishlist message if set and the property is shown
    */
    if (empty($_SESSION['insert_to_wishlist']['text'])) {
        unset($_SESSION['insert_to_wishlist']);
        $placeholders = [
            '[action-id]' => 'hidden-id',
            '[action-class]' => 'none',
            '[action-status-msg]' => '' // Empty message for hidden div
        ];
        $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
    } else {
        $msg = $_SESSION['insert_to_wishlist'];
        unset($_SESSION['insert_to_wishlist']);
        $placeholders = [
            '[action-id]' => 'insert-wishlist-id',
            '[action-class]' => $msg['type'] === 'error'
                ? 'error-msg display-msg'
                : 'success-msg display-msg',
            '[action-status-msg]' => $msg['text']
        ];
        $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
    }
} else {
    //2)
    $_SESSION['show_prop_msg'] = [
        'type' => 'error',
        'text' => '<p>Spiacenti, la proprietà selezionata non esiste.</p>'
    ];
    header('location: proprieta.php');
    exit();
}


$paginaHTML = str_replace("[dettagli]", $dettagli_proprieta, $paginaHTML);
echo $paginaHTML;
exit();
?>