<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

/*
    User check:
    user -> change psw
    none -> redirect to 403.html
*/
if (!isset($_SESSION['email'])) {
    header("Location: /403.html"); 
    exit();
}

$paginaHTML = file_get_contents('../html/modifica_password.html');
$email = "";
$old = "";
$new = "";
$emailErr = "";
$old_pswErr = "";
$new_pswErr = "";
$formValido = true;

/*
    1. user visit modifica_password.php via link      -> redirect to areariservata.php
    2. user visit modifica_password.php via form      -> redirect to areariservata.php
*/
if ($_SERVER["REQUEST_METHOD"] !== "POST"){
    echo $paginaHTML;
    exit(); //????? serve
}
$email = trim($_POST['email'] ?? '');
$old   = $_POST['old_password'] ?? '';
$new   = $_POST['new_password'] ?? '';
$email = trim($_POST['email'] ?? '');

//prima validazione dei campi
if($email === '') {
    $emailErr .= '<p><span lang="en">Email</span> non inserita</p>';
    $formValido = false;
} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //aggiungere controllo = delle due password
    $emailErr .= '<p>Inserire un <span lang="en">email</span> valida.</p>';
    $formValido = false;
}
if($old === ''){
    $old_pswErr .= '<p><span lang="en">Password</span> attuale non inserita</p>';
    $formValido = false;
}
if($new === ''){
    $new_pswErr .= '<p>Nuova <span lang="en">password</span> non inserita</p>';
    $formValido = false;
}

//secondi controlli dei campi tramite db
if($formValido) {
    $connessione = new DBAccess();
    $connessioneOK = $connessione->openDBConnection();
    if ($connessioneOK) {
        if($email !== $_SESSION["email"]){
            $emailErr .= "<p>L'<span lang='en'>email</span> inserita non corrisponde a quella del tuo <span lang='en'>account</span>.</p>";
            $formValido = false;
        } 
        if($formValido) { //se la email è valida ed uguale a quella dell'account allora procedo con altre validazioni
            $oldpswResult = $connessione->checkOldPassword($email, $old);
            if($oldpswResult['success'] && $oldpswResult['content'] == "PASSWORD_INVALID") {
                $old_pswErr .= "<p>La <span lang='en'>password</span> inserita non corrisponde a quella del tuo <span lang='en'>account</span>.</p>";
                $formValido = false;
            } else if(!$oldpswResult['success']){
                header("Location: /500.html");
                exit();
            }
            //validazione new password
            if (strlen($new) < 6) {
                $new_pswErr .= '<p>La <span lang="en">password</span> deve contenere almeno 6 caratteri</p>';
                $formValido = false;
            }
        } else { //email valida ma non uguale a quella dell'account -> mostro già gli errori
            $paginaHTML = str_replace('[email_err]', $emailErr, $paginaHTML);
            $paginaHTML = str_replace('[old_password_err]', $old_pswErr, $paginaHTML);
            $paginaHTML = str_replace('[new_password_err]', $new_pswErr, $paginaHTML);
            echo $paginaHTML;
        }
    } else { //problemi con la connessione al DB
        header("Location: /500.html");
        exit();
    }
} 

else { // il form è gia non valido verificando solo i primi controlli
    $paginaHTML = str_replace('[email_err]', $emailErr, $paginaHTML);
    $paginaHTML = str_replace('[old_password_err]', $old_pswErr, $paginaHTML);
    $paginaHTML = str_replace('[new_password_err]', $new_pswErr, $paginaHTML);
    echo $paginaHTML;
}

if($formValido) { //modifica psw solo se tutte le validazioni sono avvenute correttamente
    $updatePswResult = $connessione->updatePassword($email, $new);
    $connessione->closeDBConnection();
    if($updatePswResult['success']){
        if(!$updatePswResult['content']){
            $_SESSION["update_psw_success_msg"] = 'Password aggiornata con successo!';
            header("Location: areariservata.php");
        } else{
            $_SESSION["update_psw_error_msg"] = 'Non è stato possibile aggiornare la password per problemi tecnici';
            header("Location: areariservata.php");
        }
    } else {
        header("Location: /500.html");
        exit();
    }
} else {
    $paginaHTML = str_replace('[email_err]', $emailErr, $paginaHTML);
    $paginaHTML = str_replace('[old_password_err]', $old_pswErr, $paginaHTML);
    $paginaHTML = str_replace('[new_password_err]', $new_pswErr, $paginaHTML);
    echo $paginaHTML;
}
?>