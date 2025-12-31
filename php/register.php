<?php
session_start();

$con = mysqli_connect('mysql', 'user', 'user1234', 'user');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $ruolo = 'utente';


    $query = "INSERT INTO utenti (nome, cognome, email, username, password, ruolo) VALUES (?,?,?,?,?, '$ruolo')";
    $stmt = $con->prepare($query);

    if(!$stmt){
        throw new Exception('Errore nella preparazione della query.');
    }
    $stmt->bind_param('sssss',$nome,$cognome ,$email , $username, $password);
    $stmt->execute();

    $result = $conn->prepare('SELECT id FROM utenti WHERE username = ?');
    $result->bind_param('s', $username);
    $result->execute();
    $result->fetch();

    if($result->fetch()){
        $_SESSION['id'] = $id;
        $_SESSION['ruolo'] = $ruolo;
        $_SESSION['logged_in'] = true;
        header("Location: proprieta.php");
    }
mysqli_stmt_close($stmt);
}
?>




}