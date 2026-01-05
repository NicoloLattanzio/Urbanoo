<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

$paginaHTML = file_get_contents('../html/dettagli_proprieta.html');

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();
$_SESSION['show_prop_msg'] = [
    'type' => '',
    'text' => ''
];

if ($connessioneOK) {
    $idProprieta = $_GET['id'] ?? '';
    if(empty($idProprieta)) {
        $_SESSION['show_prop_msg'] = [
            'type' => 'error',
            'text' => 'Spiacenti, non hai selezionato alcuna proprietà.'
        ];
        header('location: /php/proprieta.php');
        exit();
    }

    //la proprietà è stata selezionata -> controllare se esiste nel db
    $proprieta = $connessione->showProprietaDetails($idProprieta);
    $connessione->closeDBConnection();
    if ($proprieta) {
        if($proprieta['disponibilita']){ 
            $disp = 'Disponibile';
        } else {
            $disp = 'Non Disponibile';
        }
        // Sostituisci i segnaposto con i dettagli della proprietà
        $dettagli_proprieta = str_replace(
            ["[immagine]", "[nome]", "[descrizione]", "[prezzo]", "[indirizzo]", "[citta]", "[tipologia]", "[metri_quadri]", "[locali]", "[disponibilita]"],
            [$proprieta['immagine'], $proprieta['nome'], $proprieta['descrizione'], $proprieta['prezzo'], $proprieta['indirizzo'], $proprieta['citta'], $proprieta['tipologia'], $proprieta['metri_quadri'], $proprieta['locali'], $disp],
            "<div>
                <div>
                    <img src='[immagine]'>
                    <h2>[nome]</h2>
                    <a href='/php/wishlist.php?add=".$idProprieta." class='add-wishlist-btn'>Aggiungi alla wishlist</a>
                </div>
                <div>
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
        $_SESSION['show_prop_msg'] = [
            'type' => 'error',
            'text' => 'Spiacenti, la proprietà selezionata non esiste.'
        ];
        header('location: /php/proprieta.php');
        exit();
    }
} else {
    // Gestione errore connessione DB
    header("location: /500.html");
    exit();
}

$paginaHTML = str_replace("[dettagli]", $dettagli_proprieta, $paginaHTML);
echo $paginaHTML;