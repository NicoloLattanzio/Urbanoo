<?php

require_once "dbconnection.php";
use DB\DBAccess;

$paginaHTML = file_get_contents('../html/dettagli_proprieta.html');

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

if ($connessioneOK) {
    if (isset($_GET['id'])) {
        $idProprieta = $_GET['id'];
    } else {
        // id mancante → gestisci errore o redirect
        die('ID proprietà non specificato');
    }
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
        $dettagli_proprieta = "<p>Dettagli della proprietà non disponibili.</p>";
    }
} else {
    // Gestione errore connessione DB
    $dettagli_proprieta = "<p>I sistemi sono momentaneamente fuori servizio, ci scusiamo per il disagio. Ci stiamo occupando del problema, riprova più tardi oppure contattaci a questa mail: help@urbanoo.com</p>";
}

$paginaHTML = str_replace("[dettagli]", $dettagli_proprieta, $paginaHTML);
echo $paginaHTML;