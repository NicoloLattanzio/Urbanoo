<?php
// require_once -> importo una sola volta le risorse
//require_once ".." . DIRECTORY_SEPARATOR . "php". DIRECTORY_SEPARATOR . "dbConnection.php";

require_once "dbConnection.php"
use DB\DBAccess;

//$paginaHTML = file_get_contents('..' . DIRECTORY_SEPARATOR .'php'. DIRECTORY_SEPARATOR . 'squadra_php.html');
$paginaHTML = file_get_contents('squadra_php.html');

$connessione = new DBAccess();

$connessioneOK = $connessione->openDBConnection();

$atleti = "";
$stringaAtleti = "";
$paginaHTML = "";

if ($connessioneOK) {
	$atleti = $connessione->getList();
	$connessione->closeConnection();

	if($atleti != null){ // oppure count($atleti)>0

	}
	$stringaAtleti .= '<dl class="atleti">';     //.= concateno con quello che c'e' prima
	foreach($atleti as $atleta){
		$stringaAtleti .= '<dt>' . $atleta['nome'];    //""
		if($atleta['capitano']){
			$stringaAtleti .= " - <em>Capitano</em>";
		}
		$stringaAtleti .= '/<dt>'
		$stringaAtleti .= '<dd><img src=""' . $atleta['immagine'] . '"alt="" />'
						. '<dt>Data di nascita:</dt>'
						. '<dd>' . $atleta['dataNascita'] . '</dd>'
					// e via cosi', troppa roba
						. '<dt>Ruolo</dt>';
					if ($atleta['ruolo'!="libero"]){
						$stringaAtleti .= '<dt>Punti totali</dt>';
					} else {
						$stringaAtleti .= '<dt>Ricezioni</dt>';
					}
					$stringaAtleti .= '<dd>'  //troppa roba da scrivere
	}

	$stringaAtleti .= '<dl>';

} else {
	$stringaAtleti = "<p>I sistemi sono momentaneamente fuori servizio, ci scusiamo per il disagio. Ci stiamo occupando del problema, riprova piu' tardi oppure contattaci a questa mail: aiuto@pippo.com</p>";
}

$paginaHTML = str_replace("[listaAtleti]", $stringaAtleti, $paginaHTML);
echo $paginaHTML;

?>