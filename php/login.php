<?php
session_start();
require_once 'dbconnection.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    $email = trim($_POST['email']);
    $password = $_POST['password'];


    $sql = "SELECT id, nome, cognome, password, ruolo";
    $result = pg_query_params($db, $sql, array($email));
    
    if ($result) {
        $user = pg_fetch_assoc($result);
        

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_ruolo'] = $user['ruolo'];

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