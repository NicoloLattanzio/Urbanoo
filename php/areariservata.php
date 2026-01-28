<?php
session_start();

$paginaHTML = file_get_contents('../html/areariservata.html');

/*
    User role check:
    admin -> show areariservata.php without wishlist button
    user -> show areariservata.php with wishlist button
    none -> redirect to 403.html

    if user/admin try to register but already logged in -> show info message
    if admin try to access wishlist -> redirect to areariservata.php with info message
*/

if(!isset($_SESSION['role'])){
    header("Location: 403.php");
    exit();
} else {
    $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}
if (!empty($_SESSION['permission']['text'])) {
    $msg = $_SESSION['permission'];
    unset($_SESSION['permission']);
    $placeholders = [
        '[action-id]' => 'info-id',
        '[action-class]' => 'info-msg display-msg',
        '[action-status-msg]' => $msg['text']
    ];
    $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
} else {
    $placeholders = [
        '[action-id]' => 'hidden-id',
        '[action-class]' => 'none',
        '[action-status-msg]' => '' // Empty message for hidden div
    ];
    $paginaHTML = str_replace(array_keys($placeholders), array_values($placeholders), $paginaHTML);
}
$paginaHTML = str_replace("[nome]", $_SESSION['name'], $paginaHTML);
$paginaHTML = str_replace("[ruolo]", $_SESSION['role'], $paginaHTML);

if ($isAdmin) {
    //admin: remove wishlist button
    $paginaHTML = str_replace("[wishlist]", "", $paginaHTML);
} else {
    //user: add wishlist button
    $paginaHTML = str_replace("[wishlist]", "<li><a href=\"wishlist.php\" id=\"wishlist\">La mia <span lang=\"en\">wishlist</span></a></li>", $paginaHTML);
}
echo $paginaHTML;
exit();
?>