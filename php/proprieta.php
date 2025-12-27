<!-- fa andare l'utente in proprieta.html in base al suo status: user/admin
 in base allo status cambia il contenuto della pagina:  user -> mostra tutte le proprietà con pulsante view details
                                                        admin -> mostra tutte le proprietà con pulsanti edit/remove e un pulsante add in alto a dx  -->

<?php
session_start(); // Fondamentale per leggere lo stato dell'utente
require_once "dbconnection.php";
use DB\DBAccess;

//$paginaHTML = file_get_contents('..' . DIRECTORY_SEPARATOR .'php'. DIRECTORY_SEPARATOR . 'proprieta.html');
$paginaHTML = file_get_contents('proprieta.html');

// Controllo dello stato utente (Admin o User)
// Assumiamo che salvate il ruolo in $_SESSION['ruolo'] al momento del login
$isAdmin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');

$connessione = new DBAccess();
$connessioneOK = $connessione->openDBConnection();

$stringaProprieta = "";

if ($connessioneOK) {
    $listaProprieta = $connessione->getListProprieta();
    $connessione->closeConnection();

    if (!empty($listaProprieta)) {
        // Se Admin, aggiungiamo il pulsante "Aggiungi Nuova Proprietà" in alto
        if ($isAdmin) {
            $stringaProprieta .= '<div class="admin-controls"><a href="aggiungi_proprieta.php" class="btn-add">➕ Aggiungi Nuova Proprietà</a></div>';
        }

        $stringaProprieta .= '<ul class="property-list">'; 
        foreach ($listaProprieta as $proprieta) {
            $stringaProprieta .= '<li>';
            $stringaProprieta .= '<h3>' . htmlspecialchars($proprieta['nome']) . '</h3>';
            $stringaProprieta .= '<img src="' . $proprieta['immagine'] . '" alt="Foto di ' . htmlspecialchars($proprieta['nome']) . '" />';
            
            if ($isAdmin) {
    			// L'admin vede "Modifica" che va alla pagina dettagli
    			$stringaProprieta .= '<a href="dettagli_proprieta.php?id=' . $proprieta['id'] . '" aria-label="Modifica i dettagli di ' . $proprieta['nome'] . '">Modifica</a>';
    			// L'admin vede "Elimina" che attiva uno script di cancellazione  [CONTROLLA CHE SI FACCIA COSI]
    			$stringaProprieta .= '<a href="elimina_azione.php?id=' . $proprieta['id'] . '" onclick="return confirm(\'Sei sicuro di voler eliminare questa proprietà?\')" aria-label="Elimina ' . $proprieta['nome'] . '">Elimina</a>';
			} else {
    			// L'utente normale vede solo "Vedi"
    			$stringaProprieta .= '<a href="dettagli_proprieta.php?id=' . $proprieta['id'] . '" aria-label="Vedi i dettagli di ' . $proprieta['nome'] . '">Vedi</a>';
		}
		$stringaProprieta .= '</li>';
    }
    $stringaProprieta .= '</ul>';
    } else {
        $stringaProprieta = "<p>Nessuna proprietà trovata nel database.</p>";
    }
} else {
    $stringaProprieta = '<p>I sistemi sono momentaneamente fuori servizio, ci scusiamo per il disagio. Ci stiamo occupando del problema, riprova più tardi oppure contattaci attraverso <a href="index.html" aria-label="pagina dei contatti">questa pagina</a></p>';
}

$paginaHTML = str_replace("[properties]", $stringaProprieta, $paginaHTML);
echo $paginaHTML;
?>

<!-- script per eliminare la proprietà pagina elimina.php -->
<?php
/* session_start();
require_once "dbconnection.php";
use DB\DBAccess;

// Controllo sicurezza: solo l'admin può chiamare questo file
if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin' && isset($_GET['id'])) {
    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $id = $_GET['id'];
        // Qui chiamerai una funzione che scriveremo in DBAccess
        $db->deleteProprieta($id); 
        $db->closeConnection();
    }
}

// Torna automaticamente alla pagina delle proprietà
header("Location: proprieta.php");
exit(); */
?>

<!-- Poi servirebbe anche aggiungere le fuzioni deleteProprieta -->