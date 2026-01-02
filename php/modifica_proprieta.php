<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: proprieta.php");
    exit();
}

$paginaHTML = file_get_contents('../html/modifica_proprieta.html');
$messaggio = "";
$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

if ($connessioneOK) {
    if (isset($_POST['submit'])) {
        $id = intval($_POST['id']);
        $nome = $_POST['nome'];
        $desc = $_POST['descrizione'];
        $prezzo = $_POST['prezzo'];
        $disp = intval($_POST['disponibilita']); // Converte il valore del select in 0 o 1

        if ($connessione->updateProprieta($id, $nome, $desc, $prezzo, $disp)) {
            $messaggio = '<p class="success-message">Modifica salvata con successo!</p>';
        } else {
            $messaggio = '<p class="error-message">Errore durante il salvataggio.</p>';
        }
    }

    $idDaCaricare = $_GET['id'] ?? $_POST['id'] ?? null;  //serve per prendere l'id sia in GET che in POST

    if ($idDaCaricare) {
        $proprieta = $connessione->showProprietaDetails(intval($idDaCaricare));
        
        if ($proprieta) {
            $selSi = ($proprieta['disponibilita'] == 1) ? "selected" : "";
            $selNo = ($proprieta['disponibilita'] == 0) ? "selected" : "";

            $paginaHTML = str_replace(
                ["[id]", "[nome]", "[descrizione]", "[prezzo]", "[indirizzo]", "[citta]", "[metri_quadri]", "[select_si]", "[select_no]", "[messaggio]"],
                [$proprieta['id'], $proprieta['nome'], $proprieta['descrizione'], $proprieta['prezzo'], $proprieta['indirizzo'], $proprieta['citta'], $proprieta['metri_quadri'], $selSi, $selNo, $messaggio],
                $paginaHTML
            );
        }
    }
    $connessione->closeDBConnection();
}

echo $paginaHTML;
?>