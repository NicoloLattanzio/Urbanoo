<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

$paginaHTML = file_get_contents('../html/dettagli_proprieta.html');

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
    header("location: /500.html");
    exit();
}

/*
    ID input management:
    -> no input: user asks for url/dettagli_proprieta.php                   -> 404.html
    -> empty input: user asks for url/dettagli_proprieta.php?id=            -> 404.html
    -> invalid input: user asks for url/dettagli_proprieta.php?id="uegaoef" -> 404.html
*/
$idProprieta = $_GET['id'] ?? null;
if (!ctype_digit($idProprieta) || (int)$idProprieta <= 0) {
    header('Location: /404.html');
    exit();
}
$idProprieta = (int)$idProprieta;


/*
    DB output management:
    -> [true, $row]: query returned a result        -> show the selected property   1)
    -> [true, null]: query returned an empty result -> property not existent error  2)
    -> [false, DB_ERROR]: query failed              -> 500.html                     3)
*/
$showResult = $connessione->showPropertyDetails($idProprieta);
$connessione->closeDBConnection();
if(!$showResult["success"]){
    //3)
    header("location: /500.html");
    exit();
}

$proprieta = $showResult["content"];
if ($proprieta) {
    //1)
    $disp = $proprieta['disponibilita'] ? 'Disponibile' : 'Non disponibile';
    //Replace placeholders
    $dettagli_proprieta = str_replace(
        ["[immagine]", "[nome]", "[descrizione]", "[prezzo]", "[indirizzo]", "[citta]", "[tipologia]", "[metri_quadri]", "[locali]", "[disponibilita]"],
        [e($proprieta['immagine']), e($proprieta['nome']), e($proprieta['descrizione']), e($proprieta['prezzo']), e($proprieta['indirizzo']), e($proprieta['citta']), e($proprieta['tipologia']), e($proprieta['metri_quadri']), e($proprieta['locali']), $disp],
        "<div class = 'prop-details'>
            <div class = 'prop-cover'>
                <img src='[immagine]'>
                <h2>[nome]</h2>
                <a href='/php/wishlist.php?add=".e($idProprieta)." class='add-wishlist-btn'>Aggiungi alla <span lang='en'>wishlist</span></a>
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
            </div>
        </div>"
    );
} else {
    //2)
    $_SESSION['show_prop_msg'] = [
        'type' => 'error',
        'text' => 'Spiacenti, la proprietà selezionata non esiste.'
    ];
    header('location: /php/proprieta.php');
    exit();
}


$paginaHTML = str_replace("[dettagli]", $dettagli_proprieta, $paginaHTML);
echo $paginaHTML;