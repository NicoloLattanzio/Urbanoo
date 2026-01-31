<?php
session_start();

$paginaHTML = file_get_contents('../html/areariservata.html');
$areariservataMenu = "";
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

if ($isAdmin) {
    //admin: remove wishlist button
    $areariservataMenu = '  <ul id="actionList">
                                <li><a href="modifica_password.php" id="changepw">Modifica <span lang="en">password</span></a></li>
                                <li><a href="logout.php" lang="en" id="logout">Logout</a></li>
                            </ul>';
    $paginaHTML = str_replace("[areariservata_menu]", $areariservataMenu, $paginaHTML);
} else {
    //user: add wishlist button
    $areariservataMenu = '  <ul id="actionList">
                                <li><a href="wishlist.php" id="wishlist">La mia wishlist</a></li>
                                <li><a href="modifica_password.php" id="changepw">Modifica <span lang="en">password</span></a></li>
                                <li><a href="logout.php" lang="en" id="logout">Logout</a></li>
                            </ul>';
    $paginaHTML = str_replace("[areariservata_menu]", $areariservataMenu, $paginaHTML);
}
echo $paginaHTML;
exit();
?>