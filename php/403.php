<?php
session_start();
$paginaHTML = file_get_contents('../html/403.html');

echo $paginaHTML;
exit()
?>