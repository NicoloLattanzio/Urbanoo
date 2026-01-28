<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

function cleanInput($value, $tagPermessi = ''){
 	$value = trim($value);
  	$value = strip_tags($value,$tagPermessi);
  	return $value;
}
//function to print html values
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/*
    User role check:
    admin -> can change a property
    user -> redirect to 403.php
    none -> redirect to 403.php
*/
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: 403.php");
    exit();
}
$paginaHTML = file_get_contents('../html/modifica_proprieta.html');
$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

$tagPermessi ='<em><strong><ul><li>';
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

if(!$connessioneOK){
    //DB connection error
    header("location: 500.php");
    exit();
}

/*
    Differentiation between page display and form submission:
        - page display: show the form with pre-filled data of the selected property 1)
        - form submission: process the form data and update the property            2)
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    //1)
    $idProprieta = trim(string: $_GET['id'] ?? '');
    if(!$idProprieta){
        // no property selected
        $_SESSION['change_prop_msg'] = [
            'type' => 'error',
            'text' => 'Spiacenti, non hai selezionato alcuna proprietà da modificare.'
        ];
        header('location: proprieta.php');
        exit();
    } else if ($idProprieta && (!is_numeric($idProprieta) || intval($idProprieta) <= 0)){
        // invalid property id
        $_SESSION['change_prop_msg'] = [
            'type' => 'error',
            'text' => 'Seleziona una proprietà valida.'
        ];
        header('location: proprieta.php');
        exit();
    }
    // valid property id
    $idProprieta = intval($idProprieta);
    /*
        DB output management:
        -> [true, $row]: query returned a result       
                -> property: display it             1A)
                -> null: return a msg               1B)
        -> [false, DB_ERROR]: query failed
                -> 500.php                          2A)
    */
    $result = $connessione->showPropertyDetails($idProprieta);
    if(!$result['success']){
        //2A)
        header('location: 500.php');
        exit();
    }
    $proprieta = $result['content'];
    if(!$proprieta) {
        //1B)
        $_SESSION['change_prop_msg'] = [
            'type' => 'error',
            'text' => 'Spiacenti, la proprietà selezionata non esiste.'
        ];
        header('location: proprieta.php');
        exit();
    }
    //1A)
    $available = ($proprieta['disponibilita'] == 1) ? "selected" : "";
    $unavailable = ($proprieta['disponibilita'] == 0) ? "selected" : "";

    $paginaHTML = str_replace(
        ["[id_val]", "[name_val]", "[description_val]", "[price_val]", "[select_available]", "[select_unavailable]"],
        [$proprieta['id'], e($proprieta['nome']), e($proprieta['descrizione']), e($proprieta['prezzo']), $available, $unavailable],
        $paginaHTML
    );
    //no errors on initial page load
    $paginaHTML = str_replace(
        ["[id_err]", "[name_err]", "[description_err]", "[price_err]", "[availability_err]"],
        ["", "", "", ""],
        $paginaHTML
    );
    echo $paginaHTML;
    exit();
} else {
    //2)
    $id = trim(string: $_POST['id'] ?? '');
    if(!$id){
        // no property selected
        $_SESSION['change_prop_msg'] = [
            'type' => 'error',
            'text' => 'Spiacenti, non hai selezionato alcuna proprietà da modificare.'
        ];
        header('location: proprieta.php');
        exit();
    } else if ($id && (!is_numeric($id) || intval($id) <= 0)){
        // invalid property id
        $_SESSION['change_prop_msg'] = [
            'type' => 'error',
            'text' => 'Seleziona una proprietà valida.'
        ];
        header('location: proprieta.php');
        exit();
    }
    // valid property id
    $id = intval($id);
    /*
        Input validation:
            - name: empty | numbers | size
            - description: empty | size
            - price: empty | float number 2dec | positive
            - availability: empty | 1/0
    */
    // === NAME ===
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

    // === DESCRIPTION ===
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
    
    // === PRICE ===
    $prezzo = trim($_POST['price'] ?? '');
    if ($prezzo === ''){
        $prezzoErr .= '<p>Prezzo non inserito</p>';
        $formValido = false;
    } else if (!is_numeric($prezzo) || intval($prezzo) <= 0) {
        $prezzoErr .= '<p>Il prezzo deve essere un numero maggiore di 0</p>';
        $formValido = false;
    } else {
        $prezzo = cleanInput($prezzo, $tagPermessi);
        $prezzo = (float)$prezzo;
    }
    

    // === AVAILABILITY ===
    $disponibilita = trim($_POST['availability'] ?? '');
    if ($disponibilita === '') {
        $disponibilitaErr .= '<p>Disponibilità non inserita</p>';
        $formValido = false;
    } else if ($disponibilita !== '1' && $disponibilita !== '0') {
        $disponibilitaErr .= '<p>Selezionare uno stato di disponibilità valido</p>';
        $formValido = false;
    } else {
        $disponibilita = cleanInput($disponibilita, $tagPermessi);
        $disponibilita = intval($disponibilita);
    } 

    /*
        Form validation:
        Valid form: update property                                     1)
            -> redirect to proprieta.php with success/failure message
        Not valid form: display errors and previous values              2)
    */
    if(!$formValido){
        //2)
        //if availability give errors then all variables are empty
        $selSi = ($disponibilita === 1) ? "selected" : "";
        $selNo = ($disponibilita === 0) ? "selected" : "";

        $paginaHTML = str_replace('[name_err]', $nomeErr, $paginaHTML);
        $paginaHTML = str_replace('[description_err]', $descrizioneErr, $paginaHTML);
        $paginaHTML = str_replace('[price_err]', $prezzoErr, $paginaHTML);
        $paginaHTML = str_replace('[availability_err]', $disponibilitaErr, $paginaHTML);

        //replace previous values
        $paginaHTML = str_replace('[name_val]', $nomeErr ? "" : e($nome), $paginaHTML);
        $paginaHTML = str_replace('[description_val]', $descrizioneErr ? "" : e($descrizione), $paginaHTML);
        $paginaHTML = str_replace('[price_val]', $prezzoErr ? "" : e($prezzo), $paginaHTML);
        $paginaHTML = str_replace('[select_available]', $selSi, $paginaHTML);
        $paginaHTML = str_replace('[select_unavailable]', $selNo, $paginaHTML);
        echo $paginaHTML;
        exit();
    }
    //1)
    /*
        DB output management:
        -> [true, null]: affected rows > 0          -> property updated successfully: display success msg   1)
        -> [true, NOT_FOUND]: affected rows = 0     -> property not existent: display error msg             2)
        -> [false, DB_ERROR]: query failed          -> 500.php                                              3)
    */
    $updateResult = $connessione->updateProperty($id, $nome, $descrizione, $prezzo, $disponibilita);
    $connessione -> closeDBConnection();
    if (!$updateResult['success']) {
        //3)
        header('location: 500.php');
        exit();
    }
    if($updateResult['content'] === 'NOT_FOUND'){
        //2)
        $_SESSION['change_prop_msg'] = [
            'type' => 'error',
            'text' => 'C\'è stato un problema con la modifica della proprietà: la proprietà selezionata non esiste.'
        ];
        header('location: proprieta.php');
        exit();
    }
    if(!$updateResult['content']){
        //1)
        $_SESSION['change_prop_msg'] = [
            'type' => 'success',
            'text' => 'Proprietà modificata con successo.'
        ];
        header('location: proprieta.php');
        exit();
    }
}
?>