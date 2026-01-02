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

$PageRegister = file_get_contents('../html/registrazione.html');

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

if (!$connessioneOK) {
    header("location: 500.html");
    exit();
}

// Verifica se l'utente è già loggato
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: areariservata.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validazione e sanitizzazione
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nome)) {
        $nomeErr = '<p>Inserire un nome.</p>';
        $formValido = false;
    }

    if (empty($cognome)) {
        $cognomeErr = '<p>Inserire un cognome.</p>';
        $formValido = false;
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { //aggiungere controllo = delle due password
        $emailErr = '<p>Inserire un email valida.</p>';
        $formValido = false;
    }

    if (empty($password) || strlen($password) < 6) {
        $passwordErr = '<p>La password deve contenere almeno 6 caratteri.</p>';
        $formValido = false;
    }

    // Controlla se l'email esiste già
    if ($formValido) {
        $existingUser = $connessione->getUser($email);
        if ($existingUser) {
            $emailErr = '<p>Email già registrata.</p>';
            $formValido = false;
        }
    }

    if ($formValido) {
        // Hash password e registra utente
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $ruolo = 'utente';

        // Inserisce il nuovo utente
        $success = $connessione->insertUser($nome, $cognome, $email, $hashedPassword, $ruolo);

        if ($success) {
            // Ottiene l'utente appena registrato
            $user = $connessione->getUser($email);

            if ($user) {
                // Setta le variabili di sessione
                $_SESSION['name'] = $user['nome'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['ruolo'];

                header("Location: areariservata.php");
                exit();
            } else {
                $emailErr = '<p>Errore durante la registrazione. Riprovare.</p>';
                $formValido = false;
            }
        } else {
            $emailErr = '<p>Errore durante la registrazione. Riprovare.</p>';
            $formValido = false;
        }
    }else{
        // Sostituisce i placeholder con gli errori
        $PageRegister = str_replace('[nome_err]', $nomeErr, $PageRegister);
        $PageRegister = str_replace('[cognome_err]', $cognomeErr, $PageRegister);
        $PageRegister = str_replace('[email_err]', $emailErr, $PageRegister);
        $PageRegister = str_replace('[password_err]', $passwordErr, $PageRegister);

        // Mantiene i valori inseriti dall'utente
        $PageRegister = str_replace('[nome_val]', $nome, $PageRegister);
        $PageRegister = str_replace('[cognome_val]', $cognome, $PageRegister);
        $PageRegister = str_replace('[email_val]', $email, $PageRegister);
        $PageRegister = str_replace('[password_val]', $password, $PageRegister);

        echo $PageRegister;
    }
} else {
        $PageRegister = str_replace('[username_err]', '', $PageRegister);
        $PageRegister = str_replace('[nome_err]', '', $PageRegister);
        $PageRegister = str_replace('[cognome_err]', '', $PageRegister);
        $PageRegister = str_replace('[email_err]', '', $PageRegister);
        $PageRegister = str_replace('[password_err]', '', $PageRegister);

        $PageRegister = str_replace('[username_val]', '', $PageRegister);
        $PageRegister = str_replace('[nome_val]', '', $PageRegister);
        $PageRegister = str_replace('[cognome_val]', '', $PageRegister);
        $PageRegister = str_replace('[email_val]', '', $PageRegister);
        $PageRegister = str_replace('[password_val]', '', $PageRegister);
        echo $PageRegister;
}
?>