<?php
session_start();
require_once "dbconnection.php";
use DB\DBAccess;

/*
    User check:
    user -> change psw
    none -> redirect to 403.php
*/
if (!isset($_SESSION['email'])) {
    header("Location: 403.php"); 
    exit();
}

$_SESSION["update_psw_msg"] = [
    'type' => '',
    'text' => ''
];
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
    //1
    $paginaHTML = str_replace('[email_err]', $emailErr, $paginaHTML);
    $paginaHTML = str_replace('[old_password_err]', $old_pswErr, $paginaHTML);
    $paginaHTML = str_replace('[new_password_err]', $new_pswErr, $paginaHTML);
    echo $paginaHTML;
    exit(); 
}

//2
$email = trim($_POST['email'] ?? '');
$old   = $_POST['old_password'] ?? '';
$new   = $_POST['new_password'] ?? '';
$email = trim($_POST['email'] ?? '');
/*
    For every validation i check for errors:
        -> errors: show page with errors
        -> no errors: keep going
*/
/*
    First validation:
        -> check for empty inputs or not valid email sintax: Huge errors
    For UX it is best to stop here the user without filling other form fields
*/
if($email === '') {
    $emailErr .= '<p><span lang="en">Email</span> non inserita.</p>';
    $formValido = false;
} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $emailErr .= '<p>Inserire un <span lang="en">email</span> valida.</p>';
    $formValido = false;
}
if($old === ''){
    $old_pswErr .= '<p><span lang="en">Password</span> attuale non inserita.</p>';
    $formValido = false;
}
if($new === ''){
    $new_pswErr .= '<p>Nuova <span lang="en">password</span> non inserita.</p>';
    $formValido = false;
}
if(!$formValido) {
    $paginaHTML = str_replace('[email_err]', $emailErr, $paginaHTML);
    $paginaHTML = str_replace('[old_password_err]', $old_pswErr, $paginaHTML);
    $paginaHTML = str_replace('[new_password_err]', $new_pswErr, $paginaHTML);
    echo $paginaHTML;
    exit();
}

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();
if (!$connessioneOK) {
    //DB connection error
    header("Location: 500.php");
    exit();
}

/*
    Second validation:
        -> check for user consistency (user who asks to change = user inserted to change)
*/
if($email !== $_SESSION["email"]){
    $emailErr .= "<p>L'<span lang='en'>email</span> inserita non corrisponde a quella con cui hai effettuato l'accesso.</p>";
    $formValido = false;
} 
if(!$formValido) {
    $paginaHTML = str_replace('[email_err]', $emailErr, $paginaHTML);
    $paginaHTML = str_replace('[old_password_err]', $old_pswErr, $paginaHTML);
    $paginaHTML = str_replace('[new_password_err]', $new_pswErr, $paginaHTML);
    echo $paginaHTML;
    exit();
}

/*
    Final validation:
        -> password mismatch check + new password check
    DB output management:
    -> [true, PASSWORD_MISMATCH]: user exists but passwords mismatch    -> error, check for newpsw also     1)
    -> [true, null]: user exists and passwords match                    -> just check for newpsw            2)
    -> [false, DB_ERROR]: query failed                                  -> 500.html                         3)
*/
$oldpswResult = $connessione->checkOldPassword($email, $old);
if($oldpswResult['success'] && $oldpswResult['content'] === "PASSWORD_MISMATCH") {
    //1)
    $old_pswErr .= "<p>La <span lang='en'>password</span> inserita non corrisponde a quella del tuo <span lang='en'>account</span>.</p>";
    $formValido = false;
} else if(!$oldpswResult['success']){
    //3)
    header("Location: 500.php");
    exit();
}
//2)
if (strlen($new) < 4) {
    $new_pswErr .= '<p>La <span lang="en">password</span> deve contenere almeno 4 caratteri</p>';
    $formValido = false;
}
if(!$formValido) {
    $paginaHTML = str_replace('[email_err]', $emailErr, $paginaHTML);
    $paginaHTML = str_replace('[old_password_err]', $old_pswErr, $paginaHTML);
    $paginaHTML = str_replace('[new_password_err]', $new_pswErr, $paginaHTML);
    echo $paginaHTML;
    exit();
}

/*
    DB output management:
    -> [true, null]: affected rows > 0                          -> password updated correctly   1)
    -> [false, NOT_FOUND]: user exists but passwords mismatch   -> email not found              2)
    -> [false, DB_ERROR]: query failed                          -> 500.php                      3)
*/
$updatePswResult = $connessione->updatePassword($email, $new);
$connessione->closeDBConnection();
if($updatePswResult['success']){
    //1)
    $_SESSION["update_psw_msg"] = [
        'type' => 'success',
        'text' => '<p>Password aggiornata con successo! Effettua l\'accesso con le nuove credenziali.</p>'
    ];
    header('location: logout.php');
    //logout to force login with new psw
    exit();
} else {
    if($updatePswResult['content'] === 'NOT_FOUND'){
        //2)
        $_SESSION["update_psw_msg"] = [
            'type' => 'error',
            'text' => '
                <p>Sembra ci sia stato un problema tecnico durante l\'aggiornamento della <span lang="en">password</span>.</p>
                <p>Effettua il <span lang="en">login</span> con le credenziali appena modificate.</p>
                <p>Se il problema persiste, contatta l\'assistenza a questo indirizzo: <a href="mailto:info@urbanoo.it">help@urbanoo.it</a>.</p>'
        ];
        header("Location: login.php");
        exit();
    } else {
        //3)
        header("Location: 500.php");
        exit();
    }
}
?>