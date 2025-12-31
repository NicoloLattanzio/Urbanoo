<?php 

require_once "dbConnection.php";
use DB\DBAccess;

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
$immagine = "";
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
$immagineErr = "";
$indirizzoErr = "";
$cittaErr = "";

function cleanInput($value){
 	$value = trim($value);
  	$value = strip_tags($value,$tagPermessi);
  	return $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //meglio di isset($_POST['submit']) a quanto pare perche se visitano la pagina con GET non invia il form
    //Validazione Nome
    $nome = $_POST['name'];
    if (strlen($nome) == 0) {
        $nomeErr .= '<p>Nome non inserito</p>';
        $formValido = false;
    } else if(strlen(trim($nome)) == 0){ 
        $nomeErr .= '<p>Il nome non può contenere soli spazi</p>';
        $formValido = false;
    } elseif (preg_match("/\d/", $nome)) {
        $nomeErr .= '<p>Il nome non può contenere numeri</p>';
        $formValido = false;
    }
    $nome = cleanInput($_POST['name']);
    if(strlen($nome) < 4){
        $nomeErr .= '<p>Il nome deve essere composto da almeno 4 caratteri</p>';
        $formValido = false;
    }

    //Validazione Descrizione
    $descrizione = $_POST['description'];
    if (strlen($descrizione) == 0) {
        $descrizioneErr .= '<p>Descrizione non inserita</p>';
        $formValido = false;
    } else if(strlen(trim($descrizione)) == 0){ 
        $descrizioneErr .= '<p>La descrizione non può contenere soli spazi</p>';
        $formValido = false;
    }
    $descrizione = cleanInput($_POST['description']);
    if(strlen($descrizione) < 10){
        $descrizioneErr .= '<p>La descrizione deve essere composta da almeno 10 caratteri</p>';
        $formValido = false;
    }

    //Validazione Prezzo
    $prezzo = $_POST['price'];
    if (!is_numeric($prezzo) || intval($prezzo) <= 0) {
        $prezzoErr .= '<p>Il prezzo deve essere un numero maggiore di 0</p>';
        $formValido = false;
    }
    $prezzo = cleanInput($_POST['price']);

    //Validazione Tipologia
    $tipologieValide = ['Monolocale', 'Bilocale', 'Trilocale', 'Villa', 'Attico', 'Rustico'];
    $tipologia = $_POST['type'];
    if (!in_array($tipologia, $tipologieValide)) {
        $tipologiaErr .= '<p>Selezionare una tipologia valida</p>';
        $formValido = false;
    }
    $tipologia = cleanInput($_POST['type']);

    //Validazione Superficie
    $superficie = $_POST['size'];
    if (!is_numeric($superficie) || intval($superficie) <= 0) {
        $superficieErr .= '<p>La superficie deve essere un numero maggiore di 0</p>';
        $formValido = false;
    }
    $superficie = cleanInput($_POST['size']);

    //Validazione Locali
    $locali = $_POST['rooms'];
    if (!is_numeric($locali) || intval($locali) <= 0) {
        $localiErr .= '<p>Il numero di locali deve essere un numero maggiore di 0</p>';
        $formValido = false;
    }
    $locali = cleanInput($_POST['rooms']);

    //Validazione Disponibilità
    $disponibilita = $_POST['availability'];
    if ($disponibilita !== '1' && $disponibilita !== '0') {
        $disponibilitaErr .= '<p>Selezionare lo stato di disponibilità</p>';
        $formValido = false;
    }
    $disponibilita = cleanInput($_POST['availability']);

    //Validazione Immagine
    $immagine = $_POST['img'];
    if (strlen($immagine) > 0 && !preg_match("/\.(jpg|jpeg|png)$/i", $immagine)) {
        $immagineErr .= '<p>Caricare un\'immagine valida (jpg, jpeg, png)</p>';
        $formValido = false;
    }
    $immagine = cleanInput($_POST['img']);

    //Validazione Indirizzo
    $indirizzo = $_POST['address'];
    if (strlen($indirizzo) == 0) {
        $indirizzoErr .= '<p>Inserire l\'indirizzo</p>';
        $formValido = false;
    } else if(!preg_match("/^(Via|Viale|Piazza|Corso|Largo|Strada|Vicolo)\s+[A-Za-zÀ-ÿ'’\s]+,\s*\d+[A-Za-z]?$/i", $indirizzo)) {
        $indirizzoErr .= '<p>Inserire un indirizzo valido (es. Via G. Verdi, 8B)</p>';
        $formValido = false;
    }
    $indirizzo = cleanInput($_POST['address']);

    //Validazione Città
    $citta = $_POST['city'];
    if (strlen($citta) == 0) {
        $cittaErr .= '<p>Inserire la città</p>';
        $formValido = false;
    } else if(!preg_match("/^[A-Za-zÀ-ÿ'.\s-]+$/u", $citta)) {
        $cittaErr .= '<p>Inserire una città valida (solo lettere, spazi, apostrofi e trattini)</p>';
        $formValido = false;
    }
    $citta = cleanInput($_POST['city']);
    if(strlen($citta) < 2){
        $cittaErr .= '<p>La città deve essere composta da almeno 2 caratteri</p>';
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
            $insertProprieta = $connessione -> insertProprieta($nome, $descrizione, $prezzo, $tipologia, $superficie, $locali, $disponibilita, $immagine, $indirizzo, $citta);
            $connessione -> closeDBConnection();             

            if($insertProprieta){
                $_SESSION['insertion_success_msg'] = "Proprietà aggiunta con successo!";
                header("Location: proprieta.php"); //reindirizzo alla pagina proprieta.php
                exit();
            }
            else {
                $_SESSION['insertion_error_message'] = "Errore durante l'esecuzione della query.";
                header("Location: proprieta.php"); //reindirizzo alla pagina di errore (problema con l'esecuzione della query)
                exit();
            }
        } else {
            //problema con la connessione al DB
            header("Location: 500.html");
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
        $paginaHTML = str_replace('[img_err]', $immagineErr, $paginaHTML);
        $paginaHTML = str_replace('[address_err]', $indirizzoErr, $paginaHTML);
        $paginaHTML = str_replace('[city_err]', $cittaErr, $paginaHTML);

        echo $paginaHTML;
        exit();
    }
}
    echo $paginaHTML; //se l'utente accede alla pagina tramite il "+"" Aggiungi Proprietà" da proprieta.php
?>