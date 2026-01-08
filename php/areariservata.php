<?php
session_start();

$paginaHTML = file_get_contents('../html/areariservata.html');

/*
    User role check:
    admin -> show areariservata.php without wishlist button
    user -> show areariservata.php with wishlist button
    none -> redirect to 403.html
*/
if(!isset($_SESSION['role'])){
    header("Location: /403.html");
    exit();
} else {
    $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}
$paginaHTML = str_replace("[nome]", $_SESSION['name'], $paginaHTML);
$paginaHTML = str_replace("[ruolo]", $_SESSION['role'], $paginaHTML);
if ($isAdmin) {
    //admin: remove wishlist button
    $paginaHTML = str_replace("[wishlist]", "", $paginaHTML);
} else {
    //user: add wishlist button
    $paginaHTML = str_replace("[wishlist]", "<li><a href=\"../php/wishlist.php\" id=\"wishlist\">La mia <span lang=\"en\">wishlist</span></a></li>", $paginaHTML);
}
echo $paginaHTML;