<?php 
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

//function to clean inputs: remove spaces start/end + remove all html tags - $tagpermessi
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
    admin -> can insert a property
    user -> redirect to 403.php
    none -> redirect to 403.php
*/
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: 403.php");
    exit();
}

$paginaHTML = file_get_contents('../html/inserisci_proprieta.html');
$formValido = true;
$tagPermessi ='<em><strong><ul><li>';
$_SESSION['insert_prop_msg'] = [
    'type' => '',
    'text' => ''
];
//Form vars
$nome = "";
$descrizione = "";
$prezzo = "";
$tipologia = "";
$superficie = "";
$locali = "";
$disponibilita = "";
$indirizzo = "";
$citta = "";
$immagini = [];
//Form error vars
$nomeErr = "";
$descrizioneErr = "";
$prezzoErr = "";
$tipologiaErr = "";
$superficieErr = "";
$localiErr = "";
$disponibilitaErr = "";
$indirizzoErr = "";
$cittaErr = "";
$immaginiErr = "";
$propertyErr = "";

//user visits inserisci_proprieta.php from "+" in proprieta.php or via url
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    $paginaHTML = str_replace('[property_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[name_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[description_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[price_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[type_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[size_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[rooms_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[availability_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[img_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[address_err]', "", $paginaHTML);
    $paginaHTML = str_replace('[city_err]', "", $paginaHTML);
    //replace previous values (empty form)
    $paginaHTML = str_replace('[name_val]', "", $paginaHTML);
    $paginaHTML = str_replace('[description_val]', "", $paginaHTML);
    $paginaHTML = str_replace('[price_val]', "", $paginaHTML);
    $paginaHTML = str_replace('[select_monolocale]', "", $paginaHTML);
    $paginaHTML = str_replace('[select_bilocale]', "", $paginaHTML);
    $paginaHTML = str_replace('[select_trilocale]', "", $paginaHTML);
    $paginaHTML = str_replace('[select_villa]', "", $paginaHTML);
    $paginaHTML = str_replace('[select_attico]', "", $paginaHTML);
    $paginaHTML = str_replace('[select_rustico]', "", $paginaHTML);
    $paginaHTML = str_replace('[size_val]', "", $paginaHTML);
    $paginaHTML = str_replace('[rooms_val]', "", $paginaHTML);
    $paginaHTML = str_replace('[select_available]', "", $paginaHTML);
    $paginaHTML = str_replace('[select_unavailable]', "", $paginaHTML);
    $paginaHTML = str_replace('[address_val]', "", $paginaHTML);
    $paginaHTML = str_replace('[city_val]', "", $paginaHTML);
    echo $paginaHTML;
    exit();
}

/*
    Input validation:
        - name: empty | numbers | size
        - description: empty | size
        - price: empty | float number 2dec | positive
        - type: empty | match array
        - size: empty | int number | positive
        - rooms: empty | int number | positive
        - availability: empty | 1/0
        - address: empty | preg match | size
        - city: empty | preg match | size
        - image: empty | upload err (client) | upload err (server) | size | mime
*/
// === NAME ===
$nome = trim($_POST['name'] ?? '');
if ($nome === '') {
    $nomeErr .= '<p>Nome non inserito.</p>';
    $formValido = false;
} else if (preg_match("/\d/", $nome)) {
    $nomeErr .= '<p>Il nome non può contenere numeri.</p>';
    $formValido = false;
}
$nome = cleanInput($nome, $tagPermessi );
if($nome !== '' && (strlen($nome) < 2 || strlen($nome) > 25)){
    $nomeErr .= '<p>Il nome deve essere composto da almeno 2 caratteri e non più di 25.</p>';
    $formValido = false;
}

// === DESCRIPTION ===
$descrizione = trim($_POST['description'] ?? '');
if ($descrizione === '') {
    $descrizioneErr .= '<p>Descrizione non inserita.</p>';
    $formValido = false;
}
$descrizione = cleanInput($descrizione, $tagPermessi);
if($descrizione !== '' && (strlen($descrizione) < 10 || strlen($descrizione) > 250)){
    $descrizioneErr .= '<p>La descrizione deve essere composta da almeno 10 caratteri e non più di 250.</p>';
    $formValido = false;
}

// === PRICE ===
$prezzo = trim($_POST['price'] ?? '');
if ($prezzo === '') {
    $prezzoErr = '<p>Prezzo non inserito.</p>';
    $formValido = false;
} else {
    $validatedPrice = filter_var($prezzo, FILTER_VALIDATE_INT);
    if ($validatedPrice === false) {
        $prezzoErr = '<p>Il prezzo deve essere un numero intero.</p>';
        $formValido = false;
    } elseif ($validatedPrice <= 0) {
        $prezzoErr = '<p>Il prezzo deve essere maggiore di 0.</p>';
        $formValido = false;
    } elseif (strlen($prezzo) > 10) {
        $prezzoErr = '<p>Il prezzo è troppo alto (massimo 10 cifre).</p>';
        $formValido = false;
    } else {
        $prezzo = $validatedPrice;
    }
}

// === TYPE ===
$tipologia = trim($_POST['type'] ?? '');
$tipologieValide = ['Monolocale', 'Bilocale', 'Trilocale', 'Villa', 'Attico', 'Rustico'];
if ($tipologia === '') {
    $tipologiaErr .= '<p>Tipologia non inserita.</p>';
    $formValido = false;
} else if (!in_array($tipologia, $tipologieValide)) {
    $tipologiaErr .= '<p>Selezionare una tipologia valida.</p>';
    $formValido = false;
}
$tipologia = cleanInput($tipologia, $tagPermessi);

// === SIZE ===
$superficie = trim($_POST['size'] ?? '');
if ($superficie === '') {
    $superficieErr .= '<p>Superficie non inserita.</p>';
    $formValido = false;
} else if ($superficie !== '' && (!ctype_digit($superficie) || intval($superficie) <= 0)) {
    $superficieErr .= '<p>La superficie deve essere un numero intero maggiore di 0.</p>';
    $formValido = false;
} else {
    $superficie = cleanInput($superficie, $tagPermessi);
    $superficie = intval($superficie);
}


// === ROOMS ===
$locali = trim($_POST['rooms'] ?? '');
if ($locali === '') {
    $localiErr .= '<p>Locali non inseriti.</p>';
    $formValido = false;
} else if ($locali !== '' && (!ctype_digit($locali) || intval($locali) <= 0)) {
    $localiErr .= '<p>Il numero di locali deve essere un numero intero maggiore di 0.</p>';
    $formValido = false;
} else {
    $locali = cleanInput($locali, $tagPermessi);
    $locali = intval($locali);
}


// === AVAILABILITY ===
$disponibilita = trim($_POST['availability'] ?? '');
if ($disponibilita === '') {
    $disponibilitaErr .= '<p>Disponibilità non inserita.</p>';
    $formValido = false;
} else if ($disponibilita !== '' && ($disponibilita !== '1' && $disponibilita !== '0')) {
    $disponibilitaErr .= '<p>Selezionare uno stato di disponibilità valido.</p>';
    $formValido = false;
} else {
    $disponibilita = cleanInput($disponibilita, $tagPermessi);
    $disponibilita = intval($disponibilita);
}


// === ADDRESS ===
$indirizzo = trim($_POST['address'] ?? '');
if ($indirizzo === '') {
    $indirizzoErr .= '<p>Indirizzo non inserito.</p>';
    $formValido = false;
} else if(!preg_match("/^(Via|Viale|Piazza|Corso|Largo|Strada|Vicolo)\s+[A-Za-zÀ-ÿ.'’\s]+,\s*\d+[A-Za-z]?$/i", $indirizzo)) {
    $indirizzoErr .= '<p>Inserire un indirizzo valido (es. Via G. Verdi, 8B).</p>';
    $formValido = false;
}
$indirizzo = cleanInput($indirizzo, $tagPermessi);
if($indirizzo !== '' && (strlen($indirizzo) < 5 || strlen($indirizzo) > 30)){
    $indirizzoErr .= '<p>L\'indirizzo deve essere composto da almeno 5 caratteri e non più di 30.</p>';
    $formValido = false;
}

// === CITY ===
$citta = trim($_POST['city'] ?? '');
if ($citta === '') {
    $cittaErr .= '<p>Città non inserita.</p>';
    $formValido = false;
} else if(!preg_match("/^[A-Za-zÀ-ÿ'.\s-]+$/u", $citta)) {
    $cittaErr .= '<p>Inserire una città valida (solo lettere, spazi, apostrofi e trattini).</p>';
    $formValido = false;
}
$citta = cleanInput($citta, $tagPermessi);
if($citta !== '' && (strlen($citta) < 2 || strlen($citta) > 20)){
    $cittaErr .= '<p>La città deve essere composta da almeno 2 caratteri e non più di 20.</p>';
    $formValido = false;
}

// === IMAGE ===
$uploadDir = __DIR__ . '/../img/';
$immaginiErr = '';
$allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png'
    ];
$maxSize = 1 * 1024 * 1024; // 1MB

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
if (isset($_FILES['img'])) {
    foreach ($_FILES['img']['tmp_name'] as $index => $tmpName) {
        // controllo se è stato caricato un file
        if ($_FILES['img']['error'][$index] === UPLOAD_ERR_NO_FILE)
            continue; // salta questo file e passa al successivo
        $fileSize = $_FILES['img']['size'][$index];
        //MIME sicuro
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmpName);
    
        if (!array_key_exists($mime, $allowedMime)) {
            $immaginiErr .= "<p>Formato non consentito per il <span lang='en'>file</span> ".e($_FILES['img']['name'][$index]).".</p>";
            $formValido = false;
            continue;
        }
        if ($fileSize > $maxSize) {
            $immaginiErr .= "<p><span lang='en'>File</span> troppo grande: ".e($_FILES['img']['name'][$index]).".</p>";
            $formValido = false;
            continue;
        }

        $extension = $allowedMime[$mime];
        $newFileName = uniqid('property_', true) . '.' . $extension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($tmpName, $destPath)) {
            $immagini[] = 'img/' . $newFileName;
        } else {
            $immaginiErr .= "<p>Errore caricamento <span lang='en'>file</span>: ".e($_FILES['img']['name'][$index]).".</p>";
            $formValido = false;
        }
    }
    if (count($immagini) === 0 && empty($immaginiErr)) {
        $immaginiErr .= '<p>Caricare almeno un\'immagine della proprietà.</p>';
        $formValido = false;
    }
} else {
    $immaginiErr .= '<p>Caricare almeno un\'immagine della proprietà.</p>';
    $formValido = false;
}

/*
    Form validation:
    Valid form: 1)
        - name already existent
        - address already existent
        -> show page with errors    A)

        - name address not already registered
        -> register new property    B)
    Not valid form: 2)
        -> show page with errors and previous values
*/
if(!$formValido){ //2)
    //if availability/type give errors then all variables are empty
    $selMono = ($tipologia === "Monolocale") ? "selected" : "";
    $selBi = ($tipologia === "Bilocale") ? "selected" : "";
    $selTri = ($tipologia === "Trilocale") ? "selected" : "";
    $selVilla = ($tipologia === "Villa") ? "selected" : "";
    $selAttico = ($tipologia === "Attico") ? "selected" : "";
    $selRustico = ($tipologia === "Rustico") ? "selected" : "";
    $selSi = ($disponibilita == 1) ? "selected" : "";
    $selNo = ($disponibilita == 0) ? "selected" : "";

    //replace error messages
    $paginaHTML = str_replace('[property_err]', $propertyErr, $paginaHTML); 
    $paginaHTML = str_replace('[name_err]', $nomeErr, $paginaHTML);
    $paginaHTML = str_replace('[description_err]', $descrizioneErr, $paginaHTML);
    $paginaHTML = str_replace('[price_err]', $prezzoErr, $paginaHTML);
    $paginaHTML = str_replace('[type_err]', $tipologiaErr, $paginaHTML);
    $paginaHTML = str_replace('[size_err]', $superficieErr, $paginaHTML);
    $paginaHTML = str_replace('[rooms_err]', $localiErr, $paginaHTML);
    $paginaHTML = str_replace('[availability_err]', $disponibilitaErr, $paginaHTML);
    $paginaHTML = str_replace('[img_err]', $immaginiErr, $paginaHTML);
    $paginaHTML = str_replace('[address_err]', $indirizzoErr, $paginaHTML);
    $paginaHTML = str_replace('[city_err]', $cittaErr, $paginaHTML);

    //replace previous values
    $paginaHTML = str_replace('[name_val]', $nomeErr ? "" : e($nome), $paginaHTML);
    $paginaHTML = str_replace('[description_val]', $descrizioneErr ? "" : e($descrizione), $paginaHTML);
    $paginaHTML = str_replace('[price_val]', $prezzoErr ? "" : e($prezzo), $paginaHTML);
    //dont need to check errors for type and availability bc they are empty if errors
    $paginaHTML = str_replace('[select_monolocale]', $selMono, $paginaHTML);
    $paginaHTML = str_replace('[select_bilocale]', $selBi, $paginaHTML);
    $paginaHTML = str_replace('[select_trilocale]', $selTri, $paginaHTML);
    $paginaHTML = str_replace('[select_villa]', $selVilla, $paginaHTML);
    $paginaHTML = str_replace('[select_attico]', $selAttico, $paginaHTML);
    $paginaHTML = str_replace('[select_rustico]', $selRustico, $paginaHTML);
    $paginaHTML = str_replace('[size_val]', $superficieErr ? "" : e($superficie), $paginaHTML);
    $paginaHTML = str_replace('[rooms_val]', $localiErr ? "" : e($locali), $paginaHTML);
    $paginaHTML = str_replace('[select_available]', $selSi, $paginaHTML);
    $paginaHTML = str_replace('[select_unavailable]', $selNo, $paginaHTML);
    $paginaHTML = str_replace('[address_val]', $indirizzoErr ? "" : e($indirizzo), $paginaHTML);
    $paginaHTML = str_replace('[city_val]', $cittaErr ? "" : e($citta), $paginaHTML);
    echo $paginaHTML;
    exit();
}

$connessione = new DBAccess();
$connessioneOK = $connessione -> openDBConnection();
if(!$connessioneOK){
    //DB connection error
    header("location: 500.php");
    exit();
}

/*
    DB output management:
    -> [true, null]: affected property rows > 0                                         -> insert successful            1)
    -> [false, ALREADY_EXISTS]: property with the same name or address already exists   -> insert not done              2)
    -> [false, INSERT_PROP_FAILED]: affected property rows = 0                          -> insert fail                  3)
    -> [false, INSERT_IMG_FAILED]: affected images rows = 0                             -> insert of an image failed    4)
    -> [false, DB_ERROR]: query failed                                                  -> 500.php                      5)
*/
$insertResult = $connessione -> insertProperty($nome, $descrizione, $prezzo, $tipologia, $superficie, $locali, $disponibilita, $immagini, $indirizzo, $citta);
$connessione -> closeDBConnection();             
if($insertResult["success"]){
    //1), 1B)
    $_SESSION['insert_prop_msg'] = [
            'type' => 'success',
            'text' => '<p>La proprietà è stata aggiunta con successo.</p>'
        ];
} else {
    if($insertResult["content"] === "ALREADY_EXISTS"){
        //2), 1A)
        $selMono = ($tipologia === "Monolocale") ? "selected" : "";
        $selBi = ($tipologia === "Bilocale") ? "selected" : "";
        $selTri = ($tipologia === "Trilocale") ? "selected" : "";
        $selVilla = ($tipologia === "Villa") ? "selected" : "";
        $selAttico = ($tipologia === "Attico") ? "selected" : "";
        $selRustico = ($tipologia === "Rustico") ? "selected" : "";
        $selSi = ($disponibilita == 1) ? "selected" : "";
        $selNo = ($disponibilita == 0) ? "selected" : "";

        $propertyErr = "<p>Esiste già una proprietà con lo stesso nome o lo stesso indirizzo.</p>";
        $paginaHTML = str_replace('[property_err]', $propertyErr, $paginaHTML);
        $paginaHTML = str_replace('[name_err]', $nomeErr, $paginaHTML);
        $paginaHTML = str_replace('[description_err]', $descrizioneErr, $paginaHTML);
        $paginaHTML = str_replace('[price_err]', $prezzoErr, $paginaHTML);
        $paginaHTML = str_replace('[type_err]', $tipologiaErr, $paginaHTML);
        $paginaHTML = str_replace('[size_err]', $superficieErr, $paginaHTML);
        $paginaHTML = str_replace('[rooms_err]', $localiErr, $paginaHTML);
        $paginaHTML = str_replace('[availability_err]', $disponibilitaErr, $paginaHTML);
        $paginaHTML = str_replace('[img_err]', $immaginiErr, $paginaHTML);
        $paginaHTML = str_replace('[address_err]', $indirizzoErr, $paginaHTML);
        $paginaHTML = str_replace('[city_err]', $cittaErr, $paginaHTML);

        //replace previous values (no name, no address, no images)
        $paginaHTML = str_replace('[name_val]', '', $paginaHTML);
        $paginaHTML = str_replace('[description_val]', e($descrizione), $paginaHTML);
        $paginaHTML = str_replace('[price_val]', e($prezzo), $paginaHTML);
        $paginaHTML = str_replace('[select_monolocale]', $selMono, $paginaHTML);
        $paginaHTML = str_replace('[select_bilocale]', $selBi, $paginaHTML);
        $paginaHTML = str_replace('[select_trilocale]', $selTri, $paginaHTML);
        $paginaHTML = str_replace('[select_villa]', $selVilla, $paginaHTML);
        $paginaHTML = str_replace('[select_attico]', $selAttico, $paginaHTML);
        $paginaHTML = str_replace('[select_rustico]', $selRustico, $paginaHTML);
        $paginaHTML = str_replace('[size_val]', e($superficie), $paginaHTML);
        $paginaHTML = str_replace('[rooms_val]', e($locali), $paginaHTML);
        $paginaHTML = str_replace('[select_available]', $selSi, $paginaHTML);
        $paginaHTML = str_replace('[select_unavailable]', $selNo, $paginaHTML);
        $paginaHTML = str_replace('[city_val]', e($citta), $paginaHTML);
        $paginaHTML = str_replace('[address_val]', '', $paginaHTML);
        echo $paginaHTML;
        exit();
    } else if ($insertResult["content"] === "INSERT_PROP_FAILED"){
        //3)
        $_SESSION['insert_prop_msg'] = [
            'type' => 'error',
            'text' => '<p>C\'è stato un problema con l\'aggiunta della proprietà.</p>'
        ];
    } else if ($insertResult["content"] === "INSERT_IMG_FAILED") {
        //4)
        $_SESSION['insert_prop_msg'] = [
            'type' => 'error',
            'text' => '<p>C\'è stato un problema con l\'aggiunta delle immagini.</p>'
        ];
    } else if ($insertResult["content"] === "DB_ERROR") {
        //5)
        header("location: 500.php");
        exit();
    }
}

//redirect with success/error messages
header("Location: proprieta.php");
exit();
?>