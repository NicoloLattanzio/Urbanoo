<?php 
session_start();
require_once "dbConnection.php";
use DB\DBAccess;

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: /403.html");
    exit();
}

$paginaHTML = file_get_contents('../html/aggiungi_proprieta.html');
$formValido = true;
$tagPermessi ='<em><strong><ul><li>';

// Variabili per i valori del form
$nome = "";
$descrizione = "";
$prezzo = "";
$tipologia = "";
$superficie = "";
$locali = "";
$disponibilita = "";
$immagini = [];
$indirizzo = "";
$citta = "";

// Variabili per i messaggi di errore
$nomeErr = "";
$descrizioneErr = "";
$prezzoErr = "";
$tipologiaErr = "";
$superficieErr = "";
$localiErr = "";
$disponibilitaErr = "";
$immaginiErr = "";
$indirizzoErr = "";
$cittaErr = "";

function cleanInput($value, $tagPermessi = ''){
 	$value = trim($value);
  	$value = strip_tags($value,$tagPermessi);
  	return $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //meglio di isset($_POST['submit']) a quanto pare perche se visitano la pagina con GET non invia il form
    //Validazione Nome
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

    //Validazione Descrizione
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

    //Validazione Prezzo
    $prezzo = trim($_POST['price'] ?? '');
    if ($prezzo === ''){
        $prezzoErr .= '<p>Prezzo non inserito</p>';
        $formValido = false;
    } else if (!is_numeric($prezzo) || intval($prezzo) <= 0) {
        $prezzoErr .= '<p>Il prezzo deve essere un numero maggiore di 0</p>';
        $formValido = false;
    }
    $prezzo = cleanInput($prezzo, $tagPermessi);

    //Validazione Tipologia
   $tipologia = trim($_POST['type'] ?? '');
    $tipologieValide = ['Monolocale', 'Bilocale', 'Trilocale', 'Villa', 'Attico', 'Rustico'];
    if ($tipologia === '') {
        $tipologiaErr .= '<p>Tipologia non inserita</p>';
        $formValido = false;
    } else if (!in_array($tipologia, $tipologieValide)) {
        $tipologiaErr .= '<p>Selezionare una tipologia valida</p>';
        $formValido = false;
    }
    $tipologia = cleanInput($tipologia, $tagPermessi);

    //Validazione Superficie
    $superficie = trim($_POST['size'] ?? '');
    if ($superficie === '') {
        $superficieErr .= '<p>Superficie non inserita</p>';
        $formValido = false;
    } else if (!is_numeric($superficie) || intval($superficie) <= 0) {
        $superficieErr .= '<p>La superficie deve essere un numero maggiore di 0</p>';
        $formValido = false;
    }
    $superficie = cleanInput($superficie, $tagPermessi);

    //Validazione Locali
    $locali = trim($_POST['rooms'] ?? '');
    if ($locali === '') {
        $localiErr .= '<p>Locali non inseriti</p>';
        $formValido = false;
    } else if (!is_numeric($locali) || intval($locali) <= 0) {
        $localiErr .= '<p>Il numero di locali deve essere un numero maggiore di 0</p>';
        $formValido = false;
    }
    $locali = cleanInput($locali, $tagPermessi);

    //Validazione Disponibilità
    $disponibilita = trim($_POST['availability'] ?? '');
    if ($disponibilita === '') {
        $disponibilitaErr .= '<p>Disponibilità non inserita</p>';
        $formValido = false;
    } else if ($disponibilita !== '1' && $disponibilita !== '0') {
        $disponibilitaErr .= '<p>Selezionare uno stato di disponibilità valido</p>';
        $formValido = false;
    }
    $disponibilita = cleanInput($disponibilita, $tagPermessi);
    //Validazione Immagine
    /*$immagine = $_POST['img'];
    if (strlen($immagine) > 0 && !preg_match("/\.(jpg|jpeg|png)$/i", $immagine)) {
        $immagineErr .= '<p>Caricare un\'immagine valida (jpg, jpeg, png)</p>';
        $formValido = false;
    }
    $immagine = cleanInput($_POST['img'], $tagPermessi);*/

    $uploadDir = __DIR__ . '/../img/';
    $immaginiErr = '';
    $formValido = true;
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
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
        
            if (!array_key_exists($mime, $allowedMime)) {
                $immaginiErr .= "<p>Formato non consentito per il <span lang='en'>file</span> ".$_FILES['img']['name'][$index]."</p>";
                continue;
            }
            if ($fileSize > $maxSize) {
                $immaginiErr .= "<p><span lang='en'>File</span> troppo grande: ".$_FILES['img']['name'][$index]."</p>";
                continue;
            }

            $extension = $allowedMime[$mime];
            $newFileName = uniqid('property_', true) . '.' . $extension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($tmpName, $destPath)) {
                $immagini[] = 'img/' . $newFileName;
            } else {
                $immaginiErr .= "<p>Errore caricamento <span lang='en'>file</span>: ".$_FILES['img']['name'][$index]."</p>";
                $formValido = false;
            }
        }
    } else {
        $immaginiErr .= '<p>Immagine non caricata</p>';
        $formValido = false;
    }

    //Validazione Indirizzo
    $indirizzo = trim($_POST['address'] ?? '');
    if ($indirizzo === '') {
        $indirizzoErr .= '<p>Indirizzo non inserito</p>';
        $formValido = false;
    } else if(!preg_match("/^(Via|Viale|Piazza|Corso|Largo|Strada|Vicolo)\s+[A-Za-zÀ-ÿ'’\s]+,\s*\d+[A-Za-z]?$/i", $indirizzo)) {
        $indirizzoErr .= '<p>Inserire un indirizzo valido (es. Via G. Verdi, 8B)</p>';
        $formValido = false;
    }
    $indirizzo = cleanInput($indirizzo, $tagPermessi);
    if(strlen($indirizzo) < 5 || strlen($indirizzo) > 30){
        $indirizzoErr .= '<p>L\'indirizzo deve essere composto da almeno 5 caratteri e non più di 30</p>';
        $formValido = false;
    }

    //Validazione Città
    $citta = trim($_POST['city'] ?? '');
    if ($citta === '') {
        $cittaErr .= '<p>Città non inserita</p>';
        $formValido = false;
    } else if(!preg_match("/^[A-Za-zÀ-ÿ'.\s-]+$/u", $citta)) {
        $cittaErr .= '<p>Inserire una città valida (solo lettere, spazi, apostrofi e trattini)</p>';
        $formValido = false;
    }
    $citta = cleanInput($citta, $tagPermessi);
    if(strlen($citta) < 2 || strlen($citta) > 20){
        $cittaErr .= '<p>La città deve essere composta da almeno 2 caratteri e non più di 20</p>';
        $formValido = false;
    }

    if($formValido){
        $connessione = new DBAccess();
        $connessioneOK = $connessione->openDBConnection();
        if($connessioneOK){
            $checkName = $connessione -> propertyNameAlreadyExistent($nome);
            $checkAddress = $connessione -> propertyAddressAlreadyExistent($indirizzo);
            if ($checkName) { //nome già esistente
                $nomeErr .= '<p>Nome già esistente</p>';
            }
            if ($checkAddress) { //indirizzo già utilizzato
                $indirizzoErr .= '<p>Indirizzo già utilizzato</p>';
            }
            if($checkName || $checkAddress){ //nome o indirizzo già utilizzati, devo rimandare al form
                $paginaHTML = str_replace('[name_err]', $nomeErr, $paginaHTML);
                $paginaHTML = str_replace('[address_err]', $indirizzoErr, $paginaHTML);
                echo $paginaHTML;
                exit();
            }
            //nome, indirizzo non ancora registrati
            //registro dati su db e reindirizzo alla pagina principale
            $insertProprieta = $connessione -> insertProprieta($nome, $descrizione, $prezzo, $tipologia, $superficie, $locali, $disponibilita, $immagini, $indirizzo, $citta);
            $connessione -> closeDBConnection();             

            if($insertProprieta){
                $_SESSION['insertion_prop_msg'] = "success";
            }
            else {
                $_SESSION['insertion_prop_msg'] = "error"; //problema esecuzione query
            }
            header("Location: proprieta.php?insertion_prop_msg"); //reindirizzo proprieta.php con relativo messaggio di successo/errore inserimento
            exit();
        } else {
            //problema con la connessione al DB
            header("Location: /500.html");
            exit();
        }
    } else { //form non valido
        //faccio visualizzare i messaggi di errore del form
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

        echo $paginaHTML;
        exit();
    }
}
    echo $paginaHTML; //se l'utente accede alla pagina tramite il "+"" Aggiungi Proprietà" da proprieta.php
?>