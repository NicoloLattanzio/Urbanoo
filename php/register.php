<?php
session_start();
require_once 'dbconnection.php';
use DB\DBAccess;

$formValido = true;
$nome = '';
$nomeErr = '';
$cognome = '';
$cognomeErr = '';
$email = '';
$emailErr = '';
$password = '';
$passwordErr = '';
$confirm_password = '';
$confirm_passwordErr = '';

$PageRegister = file_get_contents('../html/registrazione.html');
function cleanInput($value, $tagPermessi = ''){
 	$value = trim($value);
  	$value = strip_tags($value,$tagPermessi);
  	return $value;
}

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

if (!$connessioneOK) {
        header("location: 500.php");
        exit();
}

// Verifica se l'utente è già loggato
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: areariservata.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validazione e sanitizzazione
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
    
    //validazione cognome
    $cognome = trim($_POST['surname'] ?? '');
    if ($cognome === '') {
        $cognomeErr .= '<p>Cognome non inserito</p>';
        $formValido = false;
    } else if (preg_match("/\d/", $cognome)) {
        $cognomeErr .= '<p>Il cognome non può contenere numeri</p>';
        $formValido = false;
    }
    $cognome = cleanInput($cognome, $tagPermessi );
    if(strlen($cognome) < 2 || strlen($cognome) > 25){
        $cognomeErr .= '<p>Il cognome deve essere composto da almeno 2 caratteri e non più di 25</p>';
        $formValido = false;
    }

    //validazione email
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $emailErr .= '<p><span lang="en">Email</span> non inserita</p>';
        $formValido = false;
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //aggiungere controllo = delle due password
        $emailErr .= '<p>Inserire un <span lang="en">email</span> valida.</p>';
        $formValido = false;
    }

    //validazione password
    $password = $_POST['password'] ?? '';
    if ($password === '') {
        $passwordErr .= '<p><span lang="en">Password</span> non inserita</p>';
        $formValido = false;
    } else if (strlen($password) < 6) {
        $passwordErr .= '<p>La <span lang="en">password</span> deve contenere almeno 6 caratteri</p>';
        $formValido = false;
    }

    //validazione conferma password
    $confirm_password = $_POST['confirm_password'] ?? '';
    if ($confirm_password === '') {
        $confirm_passwordErr .= '<p>Conferma della <span lang="en">password</span> non inserita</p>';
        $formValido = false;
    } else if ($password !== $confirm_password) {
        $confirm_passwordErr .= '<p>Le <span lang="en">password</span> non coincidono</p>';
        $formValido = false;
    }

    // Controlla se l'email esiste già
    if ($formValido) {
        $existingUser = $connessione->getUser($email);
        if ($existingUser) {
            $emailErr .= '<p><span lang="en">Email</span> già registrata.</p>';
            $formValido = false;
        }
    }

    if ($formValido) {
        $ruolo = 'utente';
        // Inserisce il nuovo utente
        $insertResult = $connessione->insertUser($nome, $cognome, $email, $password, $ruolo);

        if ($insertResult['success']) {
            // Ottiene l'utente appena registrato
            $userResult = $connessione->getUser($email);
            if ($userResult['success']) {
                // Setta le variabili di sessione
                if(!$userResult['content']){
                    $_SESSION['name'] = $user['nome'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['ruolo'];

                    header("Location: areariservata.php");
                    exit();
                } else {
                    //critical error: user inserted but not retrievable -> change insertUser (should return the user)
                    //                                                  -> redirect to login.php with "error" msg
                    header("location: login.php");
                    exit();
                }
            } else {
                header("location: 500.php");
                exit();
            }
        } else if ($insertResult['content'] == "INSERT_FAILED"){
            $emailErr .= '<p>Errore durante la registrazione. Riprovare.</p>';
            $formValido = false;
        } else if ($insertResult['content'] == 'DB_ERROR') {
            header("location: 500.php");
            exit();
        }
    } else {
        // Sostituisce i placeholder con gli errori
        $PageRegister = str_replace('[nome_err]', $nomeErr, $PageRegister);
        $PageRegister = str_replace('[cognome_err]', $cognomeErr, $PageRegister);
        $PageRegister = str_replace('[email_err]', $emailErr, $PageRegister);
        $PageRegister = str_replace('[password_err]', $passwordErr, $PageRegister);
        $PageRegister = str_replace('[confirm_password_err]', $confirm_passwordErr, $PageRegister);

        // Mantiene i valori inseriti dall'utente
        $PageRegister = str_replace('[nome_val]', $nome, $PageRegister);
        $PageRegister = str_replace('[cognome_val]', $cognome, $PageRegister);
        $PageRegister = str_replace('[email_val]', $email, $PageRegister);

        echo $PageRegister;
        exit();
    }
} else {
        $PageRegister = str_replace('[nome_err]', '', $PageRegister);
        $PageRegister = str_replace('[cognome_err]', '', $PageRegister);
        $PageRegister = str_replace('[email_err]', '', $PageRegister);
        $PageRegister = str_replace('[password_err]', '', $PageRegister);
        $PageRegister = str_replace('[confirm_password_err]', '', $PageRegister);

        $PageRegister = str_replace('[nome_val]', '', $PageRegister);
        $PageRegister = str_replace('[cognome_val]', '', $PageRegister);
        $PageRegister = str_replace('[email_val]', '', $PageRegister);
        echo $PageRegister;
        exit();
}
?>