<?php 

require_once "dbConnection.php";
use DB\DBAccess;

$paginaHTML = file_get_contents('../html/aggiungi_proprieta.html');

$messaggiPerForm = "";

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


$tagPermessi ='<em><strong><ul><li>';

function cleanInput($value){
 	$value = trim($value);
  	$value = strip_tags($value,$tagPermessi);
  	return $value;
}

if (isset($_POST['submit'])) {
    $messaggiPerForm = "<ul>";

    // Pulizia input
    $nome = cleanInput($_POST['nome'] ?? '');
    $descrizione = cleanInput($_POST['descrizione'] ?? '');
    $prezzo = cleanInput($_POST['prezzo'] ?? '');
    $tipologia = cleanInput($_POST['tipologia'] ?? '');
    $superficie = cleanInput($_POST['superficie'] ?? '');
    $locali = cleanInput($_POST['locali'] ?? '');
    $disponibilita = cleanInput($_POST['disponibilita'] ?? '');
    $immagine = cleanInput($_POST['immagine'] ?? '');
    $indirizzo = cleanInput($_POST['indirizzo'] ?? '');
    $citta = cleanInput($_POST['citta'] ?? '');

    // Validazione Nome
    if (strlen($nome) == 0) {
        $messaggiPerForm .= "<li>Inserire il nome</li>";
    } elseif (preg_match("/\d/", $nome)) {
        $messaggiPerForm .= "<li>Il nome non può contenere numeri</li>";
    }

    // Validazione Descrizione (opzionale ma non vuota)
    if (strlen($descrizione) == 0) {
        $messaggiPerForm .= "<li>Inserire una descrizione</li>";
    }

    // Validazione Prezzo
    if (!is_numeric($prezzo) || intval($prezzo) <= 0) {
        $messaggiPerForm .= "<li>Il prezzo deve essere un numero maggiore di 0</li>";
    }

    // Validazione Tipologia
    $tipologieValide = ['Monolocale', 'Bilocale', 'Trilocale', 'Villa', 'Attico', 'Rustico'];
    if (!in_array($tipologia, $tipologieValide)) {
        $messaggiPerForm .= "<li>Selezionare una tipologia valida</li>";
    }

    // Validazione Superficie
    if (!is_numeric($superficie) || intval($superficie) <= 0) {
        $messaggiPerForm .= "<li>La superficie deve essere un numero maggiore di 0</li>";
    }

    // Validazione Locali
    if (!is_numeric($locali) || intval($locali) <= 0) {
        $messaggiPerForm .= "<li>Il numero di locali deve essere un numero maggiore di 0</li>";
    }

    // Validazione Disponibilità
    if ($disponibilita !== '1' && $disponibilita !== '0') {
        $messaggiPerForm .= "<li>Selezionare lo stato di disponibilità</li>";
    }

    // Validazione Immagine (opzionale)
    if (strlen($immagine) > 0 && !preg_match("/\.(jpg|jpeg|png)$/i", $immagine)) {
        $messaggiPerForm .= "<li>Caricare un'immagine valida (jpg, jpeg, png)</li>";
    }

    // Validazione Indirizzo
    if (strlen($indirizzo) == 0) {
        $messaggiPerForm .= "<li>Inserire l'indirizzo</li>";
    }
    else if(!preg_match("/^(Via|Viale|Piazza|Corso|Largo|Strada|Vicolo)\s+[A-Za-zÀ-ÿ'’\s]+,\s*\d+[A-Za-z]?$/i", $indirizzo)) {
            $messaggiPerForm .= "<li>Inserire un indirizzo valido (es. Via G. Verdi, 8B)</li>";
    }

    // Validazione Città
    if (strlen($citta) == 0) {
        $messaggiPerForm .= "<li>Inserire la città</li>";
    }
    else if(!preg_match("/^[A-Za-zÀ-ÿ'.\s-]+$/u", $citta)) {
            $messaggiPerForm .= "<li>Inserire una città valida (solo lettere, spazi, apostrofi e trattini)</li>";
    }

    $messaggiPerForm .= "</ul>";
}


$paginaHTML = str_replace('[messaggiForm]', $messaggiPerForm, $paginaHTML);
$paginaHTML = str_replace('[valoreNome]', $nome, $paginaHTML);
$paginaHTML = str_replace('[valData]', $dataNascita, $paginaHTML);
$paginaHTML = str_replace('[valLuogo]', $luogo, $paginaHTML);
$paginaHTML = str_replace('[valoreAltezza]', $altezza, $paginaHTML);
$paginaHTML = str_replace('[valoreSquadra]', $squadra, $paginaHTML);
$paginaHTML = str_replace('[valoreMaglia]', $maglia, $paginaHTML);
$paginaHTML = str_replace('[valRuolo]', $ruolo, $paginaHTML);
$paginaHTML = str_replace('[valoreMagliaNazionale]', $magliaNazionale, $paginaHTML);
$paginaHTML = str_replace('[valorePunti]', $punti, $paginaHTML);
$paginaHTML = str_replace('[valoreRiconoscimenti]', $riconoscimenti, $paginaHTML);
$paginaHTML = str_replace('[valoreNote]', $note, $paginaHTML);

echo $paginaHTML;

?>