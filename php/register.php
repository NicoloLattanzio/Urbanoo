<?php
session_start();
require_once 'dbconnection.php';
use DB\DBAccess;

//function to clean inputs: remove spaces start/end + remove all html tags - $tagpermessi
function cleanInput($value, $tagPermessi = ''){
 	$value = trim($value);
  	$value = strip_tags($value,$tagPermessi);
  	return $value;
}

//function to print html values
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$_SESSION['insert_user_msg'] = [
    'type' => '',
    'text' => ''
];
$_SESSION['permission'] = [
    'type' => '',
    'text' => ''
];
$formValido = true;
$nome = '';
$nomeErr = '';
$cognome = '';
$cognomeErr = '';
$username = '';
$usernameErr = '';
$email = '';
$emailErr = '';
$password = '';
$passwordErr = '';
$confirm_password = '';
$confirm_passwordErr = '';

$paginaHTML = file_get_contents('../html/registrazione.html');

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

if(!$connessioneOK){
    //DB connection error
    header("location: 500.php");
    exit();
}

// if user is already logged in, redirect to reserved area
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    $_SESSION['permission'] = [
        'type' => 'info',
        'text' => '<p>Hai già effettuato l\'accesso al sito come: ' . e($_SESSION['username']) . '.</p>'
    ];
    header("Location: areariservata.php");
    exit();
}

/*
    user visits the page for the first time: show empty form (no errors, no pre-filled values)  1)
    user submits the form: validate data                                                        2)
        if there are errors: show form with errors and pre-filled values                        2A)  
        if no errors: insert user in DB, set session variables and redirect to reserved area    2B)
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    //1)
    $paginaHTML = str_replace('[name_err]', '', $paginaHTML);
    $paginaHTML = str_replace('[surname_err]', '', $paginaHTML);
    $paginaHTML = str_replace('[username_err]', '', $paginaHTML);
    $paginaHTML = str_replace('[email_err]', '', $paginaHTML);
    $paginaHTML = str_replace('[password_err]', '', $paginaHTML);
    $paginaHTML = str_replace('[confirm_password_err]', '', $paginaHTML);

    $paginaHTML = str_replace('[name_val]', '', $paginaHTML);
    $paginaHTML = str_replace('[surname_val]', '', $paginaHTML);
    $paginaHTML = str_replace('[username_val]', '', $paginaHTML);
    $paginaHTML = str_replace('[email_val]', '', $paginaHTML);
    echo $paginaHTML;
    exit();
} 

//2)
/*
    Input validation:
        - name: empty | numbers | size
        - surname: empty | numbers | size
        - username: empty | size
        - email: empty | FILTER_VALIDATE_EMAIL
        - password: empty | size
        - confirm_password: empty | mismatch
*/
// === NAME ===
$nome = trim($_POST['name'] ?? '');
if ($nome === '') {
    $nomeErr .= '<p>Nome non inserito.</p>';
    $formValido = false;
} else if (preg_match("/\d/", $nome)) {
    $nomeErr .= '<p>Il nome non può contenere numeri.</p>';
    $formValido = false;
}
$nome = cleanInput($nome);
if($nome !== '' && (strlen($nome) < 2 || strlen($nome) > 25)){
    $nomeErr .= '<p>Il nome deve essere composto da almeno 2 caratteri e non più di 25.</p>';
    $formValido = false;
}

// === SURNAME ===
$cognome = trim($_POST['surname'] ?? '');
if ($cognome === '') {
    $cognomeErr .= '<p>Cognome non inserito.</p>';
    $formValido = false;
} else if (preg_match("/\d/", $cognome)) {
    $cognomeErr .= '<p>Il cognome non può contenere numeri.</p>';
    $formValido = false;
}
$cognome = cleanInput($cognome);
if($cognome !== '' && (strlen($cognome) < 2 || strlen($cognome) > 25)){
    $cognomeErr .= '<p>Il cognome deve essere composto da almeno 2 caratteri e non più di 25.</p>';
    $formValido = false;
}

// === USERNAME ===
$username = trim($_POST['username'] ?? '');
if ($username === '') {
    $usernameErr .= '<p><span lang="en">Username</span> non inserito.</p>';
    $formValido = false;
}
$username = cleanInput($username);
if($username !== '' && (strlen($username) < 2 || strlen($username) > 25)){
    $usernameErr .= '<p>Lo <span lang="en">username</span> deve essere composto da almeno 2 caratteri e non più di 25</p>';
    $formValido = false;
}

// === EMAIL ===
$email = trim($_POST['email'] ?? '');
if ($email === '') {
    $emailErr .= '<p><span lang="en">Email</span> non inserita.</p>';
    $formValido = false;
} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $emailErr .= '<p>Inserire un <span lang="en">email</span> valida: utente@dominio.it.</p>';
    $formValido = false;
}

// === PASSWORD ===
$password = $_POST['password'] ?? '';
if ($password === '') {
    $passwordErr .= '<p><span lang="en">Password</span> non inserita.</p>';
    $formValido = false;
} else if (strlen($password) < 4) {
    $passwordErr .= '<p>La <span lang="en">password</span> deve contenere almeno 4 caratteri.</p>';
    $formValido = false;
}

// === CONFIRM PASSWORD ===
$confirm_password = $_POST['confirm_password'] ?? '';
if ($confirm_password === '') {
    $confirm_passwordErr .= '<p>Conferma della <span lang="en">password</span> non inserita.</p>';
    $formValido = false;
} else if ($password !== $confirm_password) {
    $confirm_passwordErr .= '<p>Le <span lang="en">password</span> non coincidono.</p>';
    $formValido = false;
}

/*
    To improve user experience, check for existing username and email only if all previous validations passed.
    This way, the user will see all errors at once instead of fixing one at a time.
*/
if (!$formValido){
    //2A)
    // Shows errors and maintains user inputs
    $paginaHTML = str_replace('[name_err]', $nomeErr, $paginaHTML);
    $paginaHTML = str_replace('[surname_err]', $cognomeErr, $paginaHTML);
    $paginaHTML = str_replace('[username_err]', $usernameErr, $paginaHTML);
    $paginaHTML = str_replace('[email_err]', $emailErr, $paginaHTML);
    $paginaHTML = str_replace('[password_err]', $passwordErr, $paginaHTML);
    $paginaHTML = str_replace('[confirm_password_err]', $confirm_passwordErr, $paginaHTML);

    $paginaHTML = str_replace('[name_val]', e($nome), $paginaHTML);
    $paginaHTML = str_replace('[surname_val]', e($cognome), $paginaHTML);
    $paginaHTML = str_replace('[username_val]', e($username), $paginaHTML);
    $paginaHTML = str_replace('[email_val]', e($email), $paginaHTML);

    echo $paginaHTML;
    exit();
}

/*
    DB output management:
    -> [true, $row]: query returned a result        ->  user found by username/email -> show error of username/email    A)
    -> [true, null]: query returned an empty result ->  user not found -> proceed with registration                     B)
    -> [false, DB_ERROR]: query failed              ->  500.php                                                         C)
*/
$resultByUsername = $connessione->getUser(['username' => $username]);
if(!$resultByUsername['success']){
    //C)
    header("location: 500.php");
    exit();
}
$userByUsername = $resultByUsername['content'];
if ($userByUsername && $userByUsername['username'] === $username) {
    //A)
    $usernameErr .= '<p><span lang="en">Username</span> già registrato.</p>';
    $formValido = false;
}
$resultByEmail = $connessione->getUser(['email' => $email]);
if(!$resultByEmail['success']){
    //C)
    header("location: 500.php");
    exit();
}
$userByEmail = $resultByEmail['content'];
if ($userByEmail && $userByEmail['email'] === $email) {
    //A)
    $emailErr .= '<p><span lang="en">Email</span> già registrata.</p>';
    $formValido = false;
}

if (!$formValido){
    //2A)
    // it will show email and username errors only (if any), other errors have already been shown
    $paginaHTML = str_replace('[name_err]', $nomeErr, $paginaHTML);
    $paginaHTML = str_replace('[surname_err]', $cognomeErr, $paginaHTML);
    $paginaHTML = str_replace('[username_err]', $usernameErr, $paginaHTML);
    $paginaHTML = str_replace('[email_err]', $emailErr, $paginaHTML);
    $paginaHTML = str_replace('[password_err]', $passwordErr, $paginaHTML);
    $paginaHTML = str_replace('[confirm_password_err]', $confirm_passwordErr, $paginaHTML);

    $paginaHTML = str_replace('[name_val]', e($nome), $paginaHTML);
    $paginaHTML = str_replace('[surname_val]', e($cognome), $paginaHTML);
    $paginaHTML = str_replace('[username_val]', $usernameErr ? "" : e($username), $paginaHTML);
    $paginaHTML = str_replace('[email_val]', $emailErr ? "" : e($email), $paginaHTML);

    echo $paginaHTML;
    exit();
}

//2B), B)
$ruolo = 'utente';
$insertResult = $connessione->insertUser($nome, $cognome, $username, $email, $password, $ruolo);
/*
    DB output management:
    -> [true, null]: affected rows > 0              ->  insert successful -> get user and set session variables     A)
    -> [false, INSERT_FAILED]: affected rows = 0    ->  insert not successful -> error msg                          B)
    -> [false, DB_ERROR]: query failed              ->  500.php                                                     C)
*/

if ($insertResult['success']) {
    //A)
    // retrieves the inserted user
    /*
        DB output management:
        -> [true, $row]: query returned a result        ->  user found by username -> set session variables     D)
        -> [true, null]: query returned an empty result ->  user not found -> proceed with registration         E)
        -> [false, DB_ERROR]: query failed              ->  500.php                                             F)
    */
    $userResult = $connessione->getUser(['username' => $username]);
    
    if (!$userResult['success']){
        //F)
        header("location: 500.php");
        exit();
    }
    $user = $userResult['content'];
    if(!$user){
        //E)
        //critical error: user inserted but not retrievable -> change insertUser (should return the user)
        //                                                  -> redirect to login.php with "error" msg
        $_SESSION['insert_user_msg'] = [
            'type' => 'error',
            'text' => ' <p>Sembra ci sia stato un problema tecnico durante la registrazione.</p>
                        <p>Effettua il <span lang="en">login</span> con le credenziali appena create.</p>
                        <p>Se il problema persiste, contatta l\'assistenza a questo indirizzo: <a href="mailto:info@urbanoo.it">help@urbanoo.it</a>.</p>'
        ];
        header("location: login.php");
        exit();
    }
    //D)
    // set session variables
    $_SESSION['name'] = $user['nome'];
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['ruolo'];

    header("Location: areariservata.php");
    exit();
}

$error = $insertResult['content'];
    
if ($error === "INSERT_FAILED"){
    //B)
    $_SESSION['insert_user_msg'] = [
        'type' => 'error',
        'text' => ' <p>Sembra ci sia stato un problema tecnico durante la registrazione.</p>
                    <p>Effettua il <span lang="en">login</span> con le credenziali appena create.</p>
                    <p>Se il problema persiste, contatta l\'assistenza a questo indirizzo: <a href="mailto:info@urbanoo.it">help@urbanoo.it</a>.</p>'
    ];
    header("location: login.php");
    exit();
} else if ($error === 'DB_ERROR') {
    //C)
    header("location: 500.php");
    exit();
}
?>