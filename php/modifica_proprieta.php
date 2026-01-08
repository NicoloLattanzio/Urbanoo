<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

function cleanInput($value, $tagPermessi = ''){
 	$value = trim($value);
  	$value = strip_tags($value,$tagPermessi);
  	return $value;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /403.html");
    exit();
}

$paginaHTML = file_get_contents('../html/modifica_proprieta.html');
$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

$formValido = true;
$id = "";
$nome = "";
$nomeErr = "";
$descrizione = "";
$descrizioneErr = "";
$prezzo = "";
$prezzoErr = "";
$disponibilita = "";
$disponibilitaErr = "";

$_SESSION['change_prop_msg'] = [
    'type' => '',
    'text' => ''
];

if ($connessioneOK) {
    if(!isset($_POST['submit'])) {
        //visualizzazione della pagina con campi precompilati
        $idProprieta = trim(string: $_GET['id'] ?? '');
        //$idDaCaricare = $_GET['id'] ?? $_POST['id'] ?? null;  //serve per prendere l'id sia in GET che in POST
        if($idProprieta){
            //id validation
            if (!is_numeric($idProprieta) || intval($idProprieta) <= 0) {
                $_SESSION['change_prop_msg'] = [
                    'type' => 'error',
                    'text' => 'Seleziona una proprietà valida.'
                ];
                header('location: /php/proprieta.php');
                exit();
            }
            $idProprieta = intval($idProprieta);
            $showResult = $connessione->showProprietaDetails($idProprieta);
            if($showResult['success']) {
                if($showResult['content']){
                    $proprieta = $showResult['content'];
                    $selSi = ($proprieta['disponibilita'] == 1) ? "selected" : "";
                    $selNo = ($proprieta['disponibilita'] == 0) ? "selected" : "";

                    $paginaHTML = str_replace(
                        ["[id]", "[nome]", "[descrizione]", "[prezzo]", "[select_si]", "[select_no]"],
                        [$proprieta['id'], $proprieta['nome'], $proprieta['descrizione'], $proprieta['prezzo'], $selSi, $selNo],
                        $paginaHTML
                    );
                    echo $paginaHTML;
                    exit();
                } else {
                    $_SESSION['change_prop_msg'] = [
                        'type' => 'error',
                        'text' => 'Spiacenti, la proprietà selezionata non esiste.'
                    ];
                    header('location: /php/proprieta.php');
                    exit();
                }
            } else {
                header('location: /500.html');
                exit();
            }
        } else {
            $_SESSION['change_prop_msg'] = [
                'type' => 'error',
                'text' => 'Spiacenti, non hai selezionato alcuna proprietà da modificare.'
            ];
            header('location: /php/proprieta.php');
            exit();
        }
    } else {
        //Modifica delle informazioni di una proprietà
        //validazione e sanitizzazione degli input
        $id = trim($_POST['id'] ?? '');
        if ($id) {
            if (!is_numeric($idProprieta) || intval($idProprieta) <= 0) {
                $_SESSION['change_prop_msg'] = [
                    'type' => 'error',
                    'text' => 'Seleziona una proprietà valida.'
                ];
                header('location: /php/proprieta.php');
                exit();
            }
        } else {
            $_SESSION['change_prop_msg'] = [
                'type' => 'error',
                'text' => 'Spiacenti, non hai selezionato alcuna proprietà da modificare.'
            ];
            header('location: /php/proprieta.php');
            exit();
        }
        //validazione nome
        $nome = trim($_POST['name'] ?? '');
        if ($nome === '') {
            $nomeErr .= '<p>Nome non inserito</p>';
            $formValido = false;
        } else if (preg_match("/\d/", $nome)) {
            $nomeErr .= '<p>Il nome non può contenere numeri</p>';
            $formValido = false;
        }
        $nome = cleanInput($nome, $tagPermessi );
        if(strlen($nome) < 2 || strlen($nome) > 25){
            $nomeErr .= '<p>Il nome deve essere composto da almeno 2 caratteri e non più di 25</p>';
            $formValido = false;
        }

        //validazione descrizione
        $descrizione = trim($_POST['description'] ?? '');
        if ($descrizione === '') {
            $descrizioneErr .= '<p>Descrizione non inserita</p>';
            $formValido = false;
        }
        $descrizione = cleanInput($descrizione, $tagPermessi);
        if(strlen($descrizione) < 10 || strlen($descrizione) > 250){
            $descrizioneErr .= '<p>La descrizione deve essere composta da almeno 10 caratteri e non più di 250</p>';
            $formValido = false;
        }
        
        //validazione prezzo
        $prezzo = trim($_POST['price'] ?? '');
        if ($prezzo === ''){
            $prezzoErr .= '<p>Prezzo non inserito</p>';
            $formValido = false;
        } else if (!is_numeric($prezzo) || intval($prezzo) <= 0) {
            $prezzoErr .= '<p>Il prezzo deve essere un numero maggiore di 0</p>';
            $formValido = false;
        }
        $prezzo = cleanInput($prezzo, $tagPermessi);
        $prezzo = (float)$prezzo;

        //validazione disponibilità
        $disponibilita = trim($_POST['availability'] ?? '');
        if ($disponibilita === '') {
            $disponibilitaErr .= '<p>Disponibilità non inserita</p>';
            $formValido = false;
        } else if ($disponibilita !== '1' && $disponibilita !== '0') {
            $disponibilitaErr .= '<p>Selezionare uno stato di disponibilità valido</p>';
            $formValido = false;
        }
        $disponibilita = cleanInput($disponibilita, $tagPermessi);
        $disponibilita = intval($disponibilita);

        if($formValido){
            $updateResult = $connessione->updateProprieta($id, $nome, $descrizione, $prezzo, $disponibilita);
            if ($updateResult['success']){
                if (!$updateResult['content']){
                    $_SESSION['change_prop_msg'] = [
                        'type' => 'success',
                        'text' => 'Proprietà modificata con successo.'
                    ];
                } else {
                    $_SESSION['change_prop_msg'] = [
                        'type' => 'error',
                        'text' => 'C\'è stato un problema con la modifica della proprietà.'
                    ];
                }
                header('location: /php/proprieta.php');
                exit();
            } else {
                header('location: /500.html');
                exit();
            } 
        } else {
            //form non valido, mostro gli errori del form
            $paginaHTML = str_replace('[name_err]', $nomeErr, $paginaHTML);
            $paginaHTML = str_replace('[description_err]', $descrizioneErr, $paginaHTML);
            $paginaHTML = str_replace('[price_err]', $prezzoErr, $paginaHTML);
            $paginaHTML = str_replace('[availability_err]', $disponibilitaErr, $paginaHTML);

            echo $paginaHTML;
            exit();
        }
    }
} else {
    header('location: /500.html');
    exit();
}
?>