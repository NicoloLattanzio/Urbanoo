<?php
    session_start();
    require_once 'dbconnection.php';
    use DB\DBAccess;
    $formValido = true;
    $email = '';
    $emailErr = '';
    $password = '';
    $passwordErr = '';

    $PageAreariservata = file_get_contents('areariservata.php');
    $PageLogin = file_get_contents('../html/login.html');
    $connessione = new DBAccess();
    $connessioneOK = $connessione->openDBConnection();
    if(!$connessioneOK){
        header("location: /500.html");
        exit();
    }
    /*
        1. user already logged in as user -> redirect to areariservata.php
        2. user already logged in as admin -> redirect to areariservata.php
        3. user not logged in and form sent -> process login and validation -> if success redirect to areariservata.php, else show login form with errors
        4. user not logged in and form not sent -> show login form (login.html)
    */
    if (isset($_SESSION['user_id'], $_SESSION['role'])) {   //1,2
        header("Location: areariservata.php");
        exit;
    }
    else{
        if($_SERVER['REQUEST_METHOD'] === 'POST'){  //3
            
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = $connessione->getUser($email);
            if($user){
                if(password_verify($password, $user['password'])){
                    $_SESSION['name'] = $user['nome'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['ruolo'];
                }
                else{
                    $passwordErr .= '<p><span lang="en">Password</span> non corretta.</p>';
                    $formValido = false;
                }
            }
            else{
                $emailErr = '<p><span lang="en">Email</span> non trovata.</p>';
                $formValido = false;
            }
            if($formValido){ //login successful
                header("Location: areariservata.php");
                exit();
            }
            else{   //login failed, show login form with errors
                $PageLogin = str_replace('[email_err]', $emailErr, $PageLogin);
                $PageLogin = str_replace('[password_err]', $passwordErr, $PageLogin);
                echo $PageLogin;
            }
        }
        else{   //4
            echo $PageLogin;
        }
    }
?>