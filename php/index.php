<?php
session_start();
$paginaHTML = file_get_contents('../html/index.html');

echo $paginaHTML;
exit()
?>