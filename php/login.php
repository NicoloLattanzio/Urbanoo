<?php
session_start();
require_once 'dbconnection.php';
use DB\DBAccess;

$actionMap = [
    'insert_user_msg' => 'registration-id',
    'update_psw_msg'=> 'update-psw-id'
];
$msg = null;
$actionId = null;

foreach ($actionMap as $sessionKey => $id) {
    if (!empty($_SESSION[$sessionKey]['text'])) {
        $msg = $_SESSION[$sessionKey];
        $actionId = $id;
        unset($_SESSION[$sessionKey]);
        break; // takes only the first message found
    }
}

$formValido = true;
$username = '';
$usernameErr = '';
$password = '';
$passwordErr = '';

$paginaHTML = file_get_contents('../html/login.html');
$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();
if(!$connessioneOK){
    //DB connection error
    header("location: 500.php");
    exit();
}
/*
    1. user already logged in as user                   -> redirect to areariservata.php
    2. user already logged in as admin                  -> redirect to areariservata.php
    3. user not logged in and form sent                 -> login and validation     
    4. user not logged in and form not sent             -> show login form and eventual errors by registration redirect
    5. user forced logout from modifica_password.php    -> show login form with success message
*/
if (isset($_SESSION['user_id'], $_SESSION['role'])) {   //1,2
    header("Location: areariservata.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    //4, 5
    if (!$msg) {
        // $_SESSION[$sessionKey] has all values null
        $placeholders = [
            '[action-id]' => 'hidden-id',
            '[action-class]' => 'none',
            '[action-status-msg]' => '' // Empty message for hidden div
        ];
        $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
    } else {
        $placeholders = [
            '[action-id]' => $actionId,
            '[action-class]' => $msg['type'] === 'error'
                ? 'error-msg display-msg'
                : 'success-msg display-msg',
            '[action-status-msg]' => $msg['text']
        ];
        $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
    }
    $paginaHTML = str_replace('[username_err]', $usernameErr, $paginaHTML);
    $paginaHTML = str_replace('[password_err]', $passwordErr, $paginaHTML);
    echo $paginaHTML;
    exit();
}

//3
/*
    Essential input validation is done client side only for better UX
*/
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
/*
    DB output management:
    -> [true, $row]: query returned a result        ->  user exists, 
                                                        store session vars + 
                                                        redirect to areariservata.php   1)
    -> [true, null]: query returned an empty result ->  user not found, 
                                                        wrong credentials + 
                                                        redirect to areariservata.php   2)
    -> [false, DB_ERROR]: query failed              ->  500.php                         3)
*/
$result = $connessione->getUser(['username' => $username]);
if(!$result['success']){
    //3)
    header('location: 500.php');
    exit();
}
$user = $result['content'];
if(!$user){
    //2)
    $usernameErr = '<p><span lang="en">Username</span> non trovato.</p>';
    $formValido = false;
}
if($user && password_verify($password, $user['password'])){
    //1)
    $_SESSION['name'] = $user['nome'];
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['ruolo'];
}
else{
    //2)
    $passwordErr .= '<p><span lang="en">Password</span> non corretta.</p>';
    $formValido = false;
}

if($formValido){ 
    //login successful
    header("Location: areariservata.php");
    exit();
}
else{
    //login failed, show login form with errors
    $paginaHTML = str_replace('[username_err]', $usernameErr, $paginaHTML);
    $paginaHTML = str_replace('[password_err]', $passwordErr, $paginaHTML);
    echo $paginaHTML;
    exit();
}
?>