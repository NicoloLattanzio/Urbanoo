<?php
session_start();
$paginaHTML = file_get_contents('../html/contatti.html');

echo $paginaHTML;
exit()
?>