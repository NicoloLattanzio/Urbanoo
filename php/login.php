<?php
session_start();
require_once 'dbconnection.php';


//$con = mysqli_connect('mysql', 'user', 'user1234', 'user');

$con = new DBAccess;
$con->openDBConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    $username = $_POST['username'];
    $password = $_POST['password'];


    $query = "SELECT id, nome, cognome, ruolo, password FROM utenti WHERE username = ? AND password = ?";
    $stmt = $con->prepare($query);

    if(!$stmt){
        throw new Exception('Errore nella preparazione della query.');
    }
    $stmt->bind_param('ss',$username, $password);
    $stmt->execute();
    $stmt->bind_result($id,$nome, $cognome, $ruolo, $passwordObt);
    $stmt->fetch();


    if ($stmt->fetch()) {
        if (password_verify($password, $passwordObt)) {
            echo($nome);
            $_SESSION['id'] = $id;
            $_SESSION['logged_in'] = true;

            header("Location: proprieta.php");
            exit;
        } else {
            echo("Username o password non corretti.");
            header("Location: login.html");
        }
    }else{
        echo("Username o password non corretti.");
        header("Location: login.html");
    }
    // Chiudi lo statement
    mysqli_stmt_close($stmt);
}

?>