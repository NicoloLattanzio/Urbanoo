<?php
session_start();
$paginaHTML = file_get_contents('../html/aboutus.html');

echo $paginaHTML;
exit()
?>