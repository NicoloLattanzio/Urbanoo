<?php
session_start();
require_once 'dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    $email = trim($_POST['email']);
    $password = $_POST['password'];


    $sql = "SELECT id, nome, cognome, ruolo FROM utenti WHERE email = ? AND password = ?";
    $result = mysqli_prepare($db, $sql);
    
    if ($result) {
        mysqli_stmt_bind_param($result, "ss", $email, $password);
        mysqli_stmt_execute($result);
        $user = mysqli_stmt_get_result($result);

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['ruolo'] = $user['ruolo'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Email o password non corretti.";
        }
    } else {
        $error = "Errore nel sistema.";
    }
}
?>