<?php
namespace DB;

class DBAccess {

	private const HOST_DB = "mysql";
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

	public function closeDBConnection() {
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

		// Aggiungi questo metodo all'interno della classe DBAccess in dbconnection.php

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

	// Metodo per eliminare una proprietà dal database 
	public function deleteProprieta($id) {
    	$id = intval($id);
    	if ($id <= 0) return false;
    	$query = "DELETE FROM proprieta WHERE id = ?";
    
    	try {
        	$stmt = $this->connection->prepare($query);
        	$stmt->bind_param("i", $id);
        	$successo = $stmt->execute();

        	$deleted = $stmt->affected_rows;
        	$stmt->close();

        	return ($successo && $deleted > 0);
    	} catch (\mysqli_sql_exception $e) {
        	return false;
    	}
	}
	// Assicurati che nel database siano impostate le chiavi esterne con ON DELETE CASCADE o gestisci l'eliminazione dei record correlati nella funzione.

	// Funzione per mostrare i dettagli di una proprietà
	public function showProprietaDetails($idProprieta) {
		$query = "SELECT * FROM proprieta WHERE id = ?";
		$stmt = $this->connection->prepare($query);

    	if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];;
		}
		if (!$stmt->bind_param("i",  $idProprieta)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];;
		}
    	if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];;
		}
		$result = $stmt->get_result();
		if (!$result) {
			// Errore nel recupero del risultato della query
			error_log("Get result failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		if ($result->num_rows === 0) {
        	return ['success' => false, 'content' => 'PROP_NOT_EXISTENT'];
    	}
		return ['success' => true, 'content' => $result->fetch_assoc()];
	}


	// Funzioni per la gestione della modifica della password
	public function checkOldPassword($email, $oldPassword) {
    	$query = "SELECT * FROM utenti WHERE email = ?";
		$stmt = $this->connection->prepare($query);

    	if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];;
		}
		if (!$stmt->bind_param("s",  $email)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];;
		}
    	if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];;
		}
		$result = $stmt->get_result();
		if (!$result) {
			// Errore nel recupero del risultato della query
			error_log("Get result failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if ($result->num_rows === 0) {
        	return ['success' => false, 'content' => 'PASSWORD_INVALID'];
    	}
		$user = $result->fetch_assoc();
		$stmt->close();
        if(!password_verify($oldPassword, $user['password'])){
            return ['success' => false, 'content' => 'PASSWORD_INVALID'];;
        }
        return ['success' => true, 'content' => null];
	}

	public function updatePassword($email, $newPassword) {
		$newPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    	$query = "UPDATE utenti SET password = ? WHERE email = ?";
		$stmt = $this->connection->prepare($query);

		if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param("ss", $newPassword, $email)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
    	if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		return ['success' => true, 'content' => null];
	}

	public function propertyNameAlreadyExistent($nome) {
		$query = "SELECT * FROM proprieta WHERE nome = ?";
		$stmt = $this->connection->prepare($query);
		if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param("s", $nome)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$result = $stmt->get_result();
		if (!$result) {
			// Errore nel recupero del risultato della query
			error_log("Get result failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		if ($result->num_rows === 0) {
        	return ['success' => false, 'content' => 'NAME_NOT_EXISTENT'];
    	}
		return ['success' => true, 'content' => null];
	}

	public function propertyAddressAlreadyExistent($indirizzo) {
		$query = "SELECT * FROM proprieta WHERE indirizzo = ?";
		$stmt = $this->connection->prepare($query);
		if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param("s", $indirizzo)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$result = $stmt->get_result();
		if (!$result) {
			// Errore nel recupero del risultato della query
			error_log("Get result failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		if ($result->num_rows === 0) {
        	return ['success' => false, 'content' => 'PROP_NOT_EXISTENT'];
    	}
		return ['success' => true, 'content' => null];
	}


	public function insertProprieta($nome, $descrizione, $prezzo, $tipologia, $superficie, $locali, $disponibilita, $immagine, $indirizzo, $citta) {
		$query = "	INSERT INTO proprieta (nome, descrizione, tipologia, indirizzo, citta, prezzo, metri_quadri, locali, immagine, disponibile)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->connection->prepare($query);

		if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param("sssssdiisi", $nome, $descrizione, $tipologia, $indirizzo, $citta, $prezzo, $superficie, $locali, $immagine, $disponibilita)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		return ['success' => true, 'content' => null];
	}

	public function getUser($email) {
		$query = "SELECT * FROM utenti WHERE email = ?";
		$stmt = $this->connection->prepare($query);

		if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param("s", $email)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$result = $stmt->get_result();
		if (!$result) {
			// Errore nel recupero del risultato della query
			error_log("Get result failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		if ($result->num_rows === 0) {
        	return ['success' => false, 'content' => 'NAME_NOT_EXISTENT'];
    	}
		return ['success' => true, 'content' => $result->fetch_assoc()];
	}

	// Funzione per aggiornare i dettagli di una proprietà
	public function updateProprieta($id, $nome, $descrizione, $prezzo, $disponibilita) {
    	$query = "UPDATE proprieta SET nome = ?, descrizione = ?, prezzo = ?, disponibilita = ? WHERE id = ?";
    	$stmt = $this->connection->prepare($query);

		if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param("ssdii", $nome, $descrizione, $prezzo, $disponibilita, $id)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
   	 	if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
    	return ['success' => true, 'content' => null];
	}

    public function insertUser($nome, $cognome, $email, $password, $ruolo) {
        $query = "INSERT INTO utenti (nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param('sssss', $nome, $cognome, $email, $password, $ruolo)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
        if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		return ['success' => true, 'content' => null];
    }

	// Recupera i dettagli degli immobili salvati dall'utente loggato
	public function getWishlist($idUtente) {
		$query = "SELECT * 
				FROM proprieta p
				JOIN wishlist w ON p.id = w.id_proprieta
				WHERE w.id_utente = ?";
		$stmt = $this->connection->prepare($query);

		if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param("i", $idUtente)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query SELECT
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$result = $stmt->get_result();
		if (!$result) {
			// Errore nel recupero del risultato della query
			error_log("Get result failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		if ($result->num_rows === 0) {
        	return ['success' => false, 'content' => 'NAME_NOT_EXISTENT'];
    	}
		return ['success' => true, 'content' => $result->fetch_all(MYSQL_ASSOC)];
	}

	// Rimuove un elemento dalla wishlist
	public function removeFromWishlist($idUtente, $idProprieta) {
    	$query = "DELETE FROM wishlist WHERE id_utente = ? AND id_proprieta = ?";
    	$stmt = $this->connection->prepare($query);

		if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param("ii", $idUtente, $idProprieta)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		return ['success' => true, 'content' => null];
	}

	// Inserisce un elemento nella wishlist
	public function insertToWishlist($idUtente, $idProprieta) {
		$query = "INSERT INTO wishlist (id_utente, id_proprieta) VALUES (?, ?)";
		$stmt = $this->connection->prepare($query);

		if (!$stmt) {
			// Errore nella preparazione della query SQL
			error_log("Prepare failed: " . $this->connection->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->bind_param("ii", $idUtente, $idProprieta)) {
			// Errore nel binding dei parametri
			error_log("Bind param failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		if (!$stmt->execute()) {
			// Errore durante l'esecuzione della query
			error_log("Execute failed: " . $stmt->error);
			return ['success' => false, 'content' => 'DB_ERROR'];
		}
		$stmt->close();
		return ['success' => true, 'content' => null];
	}
}
?>