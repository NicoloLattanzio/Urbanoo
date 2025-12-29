<?php
namespace DB;

class DBAccess {

	private const HOST_DB = "localhost";
	private const DATABASE_NAME = "user";
	private const USERNAME = "user";
	private const PASSWORD = "user1234";

	private $connection;

	public function openDBConnection() {

		mysqli_report(MYSQLI_REPORT_ERROR);
		$this->connection = mysqli_connect(DBAccess::HOST_DB, DBAccess::USERNAME, DBAccess::PASSWORD, DBAccess::DATABASE_NAME);

		if (mysqli_connect_errno()){
			return false;
		} else {
			return true;
		}

	}

	public function closeConnection() {
		mysqli_close($this->connection);
	}


    // Funzione per ottenere la lista delle proprietà
	public function getListProprieta() { 

		$query = "SELECT * FROM proprieta ORDER BY nome ASC";
		$queryResult = mysqli_query($this->connection,$query) or die("Errore in dbConnection: " . mysqli_error($this->connection));
		// gli errori a schermo ci sono solo nella fase di debug
		
		if(mysqli_num_rows($queryResult) != 0){
			$result = array();
			while($row = mysqli_fetch_assoc($queryResult)){  // con riga nulla  false
				array_push($result, $row);
			}
			$queryResult->free();
			return $result;
		} else {
			return false;
		}
	} 

	// Funzione per ottenere la lista delle proprietà con filtri
	public function getFilteredProprieta($title, $city, $type, $price_min, $price_max, $size_range) {
    	// Base della query
    	$query = "SELECT * FROM proprieta WHERE 1=1";

    	// Filtro Titolo/Nome
    	if (!empty($title)) {
    	    $query .= " AND nome LIKE '%" . mysqli_real_escape_string($this->connection, $title) . "%'";
    	}
    	// Filtro Città
    	if (!empty($city)) {
    	    $query .= " AND citta LIKE '%" . mysqli_real_escape_string($this->connection, $city) . "%'";
    	}
    	// Filtro Tipologia
    	if (!empty($type)) {
    	    $query .= " AND tipologia = '" . mysqli_real_escape_string($this->connection, $type) . "'";
    	}
    	// Filtro Prezzo
    	if (!empty($price_min)) {
    	    $query .= " AND prezzo >= " . intval($price_min);
    	}
    	if (!empty($price_max)) {
    	    $query .= " AND prezzo <= " . intval($price_max);
    	}
    	// Filtro Dimensioni (basato sui valori 1, 2, 3, 4 del tuo HTML)
    	if (!empty($size_range)) {
        	switch ($size_range) {
        	    case '1': $query .= " AND metri_quadri BETWEEN 10 AND 20"; break;
        	    case '2': $query .= " AND metri_quadri BETWEEN 20 AND 60"; break;
        	    case '3': $query .= " AND metri_quadri BETWEEN 60 AND 100"; break;
        	   case '4': $query .= " AND metri_quadri > 100"; break;
        	}
    	}

    	$query .= " ORDER BY nome ASC";
    
    	$queryResult = mysqli_query($this->connection, $query);
    
    	if($queryResult && mysqli_num_rows($queryResult) != 0){
        	$result = array();
        	while($row = mysqli_fetch_assoc($queryResult)){
        	    array_push($result, $row);
        	}
        	$queryResult->free();
        	return $result;
    	}
    	return [];
	}

	// Funzione per eliminare una proprietà dal database 
	public function deleteProprieta($id) {
    	$id = intval($id);
    	if ($id <= 0) return false;

    	$query = "DELETE FROM proprieta WHERE id = $id";
    
    	try {
        	$queryResult = mysqli_query($this->connection, $query);
        	return ($queryResult && mysqli_affected_rows($this->connection) > 0);
    	} catch (\mysqli_sql_exception $e) {
       	 return false;
    	}
	}
	// Assicurati che nel database siano impostate le chiavi esterne con ON DELETE CASCADE o gestisci l'eliminazione dei record correlati nella funzione.


	// Funzioni per la gestione della modifica della password
	public function checkOldPassword($username, $oldPassword) {
    	$username = mysqli_real_escape_string($this->connection, $username);
    	$oldPassword = mysqli_real_escape_string($this->connection, $oldPassword);

    	$query = "SELECT * FROM utenti WHERE nome = '$username' AND password = '$oldPassword'";
    	$result = mysqli_query($this->connection, $query);
    
    	return (mysqli_num_rows($result) > 0);
	}

	public function updatePassword($username, $newPassword) {
    	$username = mysqli_real_escape_string($this->connection, $username);
    	$newPassword = mysqli_real_escape_string($this->connection, $newPassword);
    
    	$query = "UPDATE utenti SET password = '$newPassword' WHERE nome = '$username'";
    	return mysqli_query($this->connection, $query);
	}
}


?>



<?php
/* 
$host = "localhost";
$port = "5432";
$dbname = "urbanoo";
$user = "postgres"; 
$password = "postgres"; 

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$db = pg_connect($conn_string);

if (!$db) {
    die("Errore di connessione al database.");
} */
?> 