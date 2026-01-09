<?php
session_start();
$paginaHTML = file_get_contents('../html/404.html');

echo $paginaHTML;
exit()
?>