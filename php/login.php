<?php
session_start();
require_once 'dbconnection.php';
use DB\DBAccess;
$formValido = true;
$username = '';
$usernameErr = '';
$password = '';
$passwordErr = '';

$PageLogin = file_get_contents('../html/login.html');
$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();
if(!$connessioneOK){
    //DB connection error
    header("location: 500.php");
    exit();
}
/*
    1. user already logged in as user       -> redirect to areariservata.php
    2. user already logged in as admin      -> redirect to areariservata.php
    3. user not logged in and form sent     -> login and validation     
    4. user not logged in and form not sent -> show login form (login.php)
*/
if (isset($_SESSION['user_id'], $_SESSION['role'])) {   //1,2
    header("Location: areariservata.php");
    exit;
}
else{
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        //4
        $PageLogin = str_replace('[username_err]', $usernameErr, $PageLogin);
        $PageLogin = str_replace('[password_err]', $passwordErr, $PageLogin);
        echo $PageLogin;
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
    $result = $connessione->getUser($username);
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
        $PageLogin = str_replace('[username_err]', $usernameErr, $PageLogin);
        $PageLogin = str_replace('[password_err]', $passwordErr, $PageLogin);
        echo $PageLogin;
        exit();
    }
}
?>