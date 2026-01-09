<?php
session_start();
$paginaHTML = file_get_contents('../html/500.html');

echo $paginaHTML;
exit()
?>