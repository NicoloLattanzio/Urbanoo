<?php
namespace DB;

class DBAccess {

	private const HOST_DB = "mysql";
	private const DATABASE_NAME = "user";
	private const USERNAME = "user";
	private const PASSWORD = "user1234";

	private const USERS_TABLE = 'utenti';
	private const PROPERTIES_TABLE = 'proprieta';
	private const WISHLIST_TABLE = 'wishlist';
	private const IMAGES_TABLE = 'immagini';

	private $connection;

	public function openDBConnection() {

		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
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

	// To apply a list of filters and retrieve just the property which satisfies them
	public function getFilteredProperty($title, $city, $type, $price_min, $price_max, $size_range): array {
		try {
			// Base query
			$query  = "SELECT * FROM proprieta WHERE 1=1";
			$params = [];
			$types  = "";

			if (!empty($title)) {
				$query .= " AND nome LIKE ?";
				$params[] = "%" . $title . "%";
				$types   .= "s";
			}

			if (!empty($city)) {
				$query .= " AND citta LIKE ?";
				$params[] = "%" . $city . "%";
				$types   .= "s";
			}

			if (!empty($type)) {
				$query .= " AND tipologia = ?";
				$params[] = $type;
				$types   .= "s";
			}

			if ($price_min !== '') {
				$query .= " AND prezzo >= ?";
				$params[] = (int) $price_min;
				$types   .= "i";
			}

			if ($price_max !== '') {
				$query .= " AND prezzo <= ?";
				$params[] = (int) $price_max;
				$types   .= "i";
			}

			if (!empty($size_range)) {
				switch ($size_range) {
					case '1':
						$query .= " AND metri_quadri BETWEEN 10 AND 20";
						break;
					case '2':
						$query .= " AND metri_quadri BETWEEN 20 AND 60";
						break;
					case '3':
						$query .= " AND metri_quadri BETWEEN 60 AND 100";
						break;
					case '4':
						$query .= " AND metri_quadri > 100";
						break;
				}
			}
			$query .= " ORDER BY nome ASC";

			$stmt = $this->connection->prepare($query);
			if (!empty($params)) {
				$stmt->bind_param($types, ...$params);
			}

			$stmt->execute();
			$result = $stmt->get_result();
			$rows   = $result->fetch_all(MYSQLI_ASSOC);

			$stmt->close();
			return [
				'success' => true,
				'content' => $rows
			];
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in getFilteredProperty: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To delete a property given its ID
	public function deleteProperty($idProprieta) {
		try {
			$query = "DELETE FROM proprieta WHERE id = ?";
			$stmt = $this->connection->prepare($query);

			$stmt->bind_param("i", $idProprieta);
			$stmt->execute();

			$affectedRows = $stmt->affected_rows;
			$stmt->close();

			if ($affectedRows > 0) {
				return [
					'success' => true,
					'content' => null // rows deleted
				];
			} else {
				return [
					'success' => true,
					'content' => 'NOT_FOUND', // no rows matched the ID
				];
			}
		} catch (\mysqli_sql_exception $e) {
			// Log the error for debugging
			error_log("Database error in deleteProperty: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR',
			];
		}
	}

	// To retrieve all details from a property
	public function showPropertyDetails($idProprieta) {
		try {
			$query = "SELECT * FROM proprieta WHERE id = ?";
			$stmt = $this->connection->prepare($query);

			$stmt->bind_param("i", $idProprieta);
			$stmt->execute();

			$result = $stmt->get_result();
			$row = $result->fetch_assoc();

			$stmt->close();
			return [
				'success' => true,
				'content' => $row
			];
		} catch (\mysqli_sql_exception $e) {
			// Log the database error for debugging
			error_log("Database error in showPropertyDetails: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To check if the input password match the DB password 
	public function checkOldPassword($email, $oldPassword) {
		try {
			$query = "SELECT * FROM utenti WHERE email = ?";
			$stmt = $this->connection->prepare($query);
			$stmt->bind_param("s", $email);
			$stmt->execute();

			$result = $stmt->get_result();
			$user = $result->fetch_assoc();
			$stmt->close();

			if (!password_verify($oldPassword, $user['password'])) {
				// Password does not match
				return [
					'success' => true,
					'content' => 'PASSWORD_MISMATCH'
				];
			}
			// Password matches
			return [
				'success' => true,
				'content' => null
			];
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in checkOldPassword: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To update a user password
	public function updatePassword($email, $newPassword) {
		try {
			// Hash the new password
			$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

			$query = "UPDATE utenti SET password = ? WHERE email = ?";
			$stmt = $this->connection->prepare($query);
			$stmt->bind_param("ss", $hashedPassword, $email);
			$stmt->execute();

			$affectedRows = $stmt->affected_rows;
			$stmt->close();

			if ($affectedRows > 0) {
				return [
					'success' => true,
					'content' => null // password updated successfully
				];
			} else {
				return [
					'success' => false,
					'content' => 'NOT_FOUND' // user email does not exist
				];
			}
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in updatePassword: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To retrieve a property from proprieta or wishlist given its not null triplet (id | name | address)
	public function getProperty($table, $field) {
		try {
			$query = "SELECT * FROM $table WHERE 1=1";
			$types = '';
			$values = [];

			if(!empty($field["id"])) {
				if($table === DBaccess::WISHLIST_TABLE) {
					$query .= " AND id_proprieta = ?";
				} else {
					$query .= " AND id = ?";
				}
				$types .= 'i';
				$values[] = $field['id'];
			}
			if(!empty($field['name'])) {
				$query .= " AND nome = ?";
				$types .= 's';
				$values[] = $field['name'];
			}
			if(!empty($field['address'])) {
				$query .= " AND indirizzo = ?";
				$types .= 's';
				$values[] = $field['address'];
			}
			$stmt = $this->connection->prepare($query);
			if (!empty($values)) {
				$stmt->bind_param($types, ...$values);
			}
			$stmt->execute();

			$result = $stmt->get_result();
			$row = $result->fetch_assoc();
			$stmt->close();

			return [
				'success' => true, 
				'content' => $row
			];
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in getProperty: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To insert a property given its information
	public function insertProperty($nome, $descrizione, $prezzo, $tipologia, $superficie, $locali, $disponibilita, $immagini, $indirizzo, $citta) {
		try {
			$propertyCheckByName = $this->getProperty(DBAccess::PROPERTIES_TABLE, ['name' => $nome]);
			$propertyCheckByAddress = $this->getProperty(DBAccess::PROPERTIES_TABLE, ['address' => $indirizzo]);
			if (!$propertyCheckByName['success'] || !$propertyCheckByAddress['success']) {
				return [
					'success' => false,
					'content' => 'DB_ERROR'
				];
			}
			$propertyByName = $propertyCheckByName['content'];
			$propertyByAddress = $propertyCheckByAddress['content'];

			if(!empty($propertyByName) || !empty($propertyByAddress)) {
				return [
					'success' => false,
					'content' => 'ALREADY_EXISTS' // property already exists in properties
				];
			}
			$query = "INSERT INTO proprieta 
						(nome, descrizione, tipologia, indirizzo, citta, prezzo, metri_quadri, locali, immagine, disponibilita)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$stmt = $this->connection->prepare($query);
			$immagine_principale = $immagini[0];
			$stmt->bind_param(
				"sssssiiisi",
				$nome, 
				$descrizione, 
				$tipologia, 
				$indirizzo, 
				$citta, 
				$prezzo, 
				$superficie, 
				$locali, 
				$immagine_principale, 
				$disponibilita
			);

			$stmt->execute();
			$affectedRows = $stmt->affected_rows;
			$idProprieta = $stmt->insert_id; // prendi l'id appena inserito
			$stmt->close();

			// --- Inserimento immagini aggiuntive ---
			if (count($immagini) > 1) {
				$queryImg = "INSERT INTO immagini (id_proprieta, immagine) VALUES (?, ?)";
				$stmtImg = $this->connection->prepare($queryImg);

				for ($i = 1; $i < count($immagini); $i++) {
					$stmtImg->bind_param("is", $idProprieta, $immagini[$i]);
					$stmtImg->execute();
					$affectedRowsImg = $stmtImg->affected_rows;
					//images error handling
					if ($affectedRowsImg == 0) {
						return [
							'success' => false,
							'content' => 'INSERT_IMG_FAILED'
						];
					}
				}
				$stmtImg->close();
			}

			if ($affectedRows > 0) {
				return [
					'success' => true,
					'content' => null // insertion successful
				];
			} else {
				return [
					'success' => false,
					'content' => 'INSERT_PROP_FAILED'
				];
			}
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in insertProprieta: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To retrieve a user from its username or email
	public function getUser($field) {
		try {
			if(!empty($field['username'])) {
				$query = "SELECT * FROM utenti WHERE username = ?";
				$value = $field['username'];
			} else if(!empty($field['email'])) {
				$query = "SELECT * FROM utenti WHERE email = ?";
				$value = $field['email'];
			}
			$stmt = $this->connection->prepare($query);
			$stmt->bind_param("s", $value);
			$stmt->execute();

			$result = $stmt->get_result();
			$row = $result->fetch_assoc();
			$stmt->close(); 
			
			return [
				'success' => true, 
				'content' => $row
			];
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in getUser: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}
	
	// To retrieve all images of a property
	public function getPropertyImages($idProprieta) {
		try {
			$query = "SELECT immagine FROM immagini WHERE id_proprieta = ?";
			$stmt = $this->connection->prepare($query);
			$stmt->bind_param("i", $idProprieta);
			$stmt->execute();

			$result = $stmt->get_result();
			$listaImmagini = [];

			while ($row = $result->fetch_assoc())
				$listaImmagini[] = $row['immagine'];

			$stmt->close(); 
			
			return [
				'success' => true, 
				'content' => $listaImmagini
			];

		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in getPropertyImages: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To update a property details
	public function updateProperty($id, $nome, $descrizione, $prezzo, $disponibilita) {
		try {
			$query = "UPDATE proprieta 
					SET nome = ?, descrizione = ?, prezzo = ?, disponibilita = ? 
					WHERE id = ?";

			$stmt = $this->connection->prepare($query);
			$stmt->bind_param("ssiii", $nome, $descrizione, $prezzo, $disponibilita, $id);
			$stmt->execute();

			$affectedRows = $stmt->affected_rows;
			$stmt->close();

			if ($affectedRows > 0) {
				return [
					'success' => true,
					'content' => null // property updated successfully
				];
			} else {
				return [
					'success' => true,
					'content' => 'NOT_FOUND' // property id does not exist
				];
			}
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in updateProperty: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To insert a user with role "user"
    public function insertUser($nome, $cognome, $username, $email, $password, $ruolo) {
		try {
			// Hash the password before storing
			$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
			$query = "INSERT INTO utenti (nome, cognome, username, email, password, ruolo) 
					VALUES (?, ?, ?, ?, ?, ?)";
			$stmt = $this->connection->prepare($query);
			$stmt->bind_param('ssssss', $nome, $cognome, $username, $email, $hashedPassword, $ruolo);
			$stmt->execute();

			$affectedRows = $stmt->affected_rows;
			$stmt->close();

			if ($affectedRows > 0) {
				return [
					'success' => true,
					'content' => null // user inserted successfully
				];
			} else {
				return [
					'success' => false,
					'content' => 'INSERT_FAILED'
				];
			}
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in insertUser: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To return the list of properties the user saved in its wishlist
	public function getWishlist($idUtente) {
		try {
			$query = "SELECT
							p.id AS id,
							p.nome AS nome,
							p.descrizione AS descrizione,
							p.citta AS citta,
							p.tipologia AS tipologia,
							p.prezzo AS prezzo,
							p.metri_quadri AS metri_quadri,
							p.indirizzo AS indirizzo,
							p.locali AS locali,
							p.disponibilita AS disponibilita,
							p.immagine AS immagine
						FROM proprieta p
						JOIN wishlist w ON p.id = w.id_proprieta
						WHERE w.id_utente = ?";
			$stmt = $this->connection->prepare($query);
			$stmt->bind_param("i", $idUtente);
			$stmt->execute();

			$result = $stmt->get_result();
			$rows = $result->fetch_all(MYSQLI_ASSOC);
			$stmt->close(); 
			return [
				'success' => true, 
				'content' => $rows
			];
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in getWishlist: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To remove a property from a user wishlist
	public function removeFromWishlist($idUtente, $idProprieta) {
		try {
			$query = "DELETE FROM wishlist WHERE id_utente = ? AND id_proprieta = ?";
			$stmt = $this->connection->prepare($query);
			$stmt->bind_param("ii", $idUtente, $idProprieta);
			$stmt->execute();

			$affectedRows = $stmt->affected_rows;
			$stmt->close();

			if ($affectedRows > 0) {
				return [
					'success' => true,
					'content' => null // item successfully removed
				];
			} else {
				return [
					'success' => false,
					'content' => 'NOT_FOUND' // item not found
				];
			}
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in removeFromWishlist: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}

	// To insert a property into a user wishlist
	public function insertToWishlist($idUtente, $idProprieta) {
		try {
			$propertyCheck = $this->getProperty(DBAccess::WISHLIST_TABLE, ['id' => $idProprieta]);
			if (!$propertyCheck['success']) {
				return [
					'success' => false,
					'content' => 'DB_ERROR'
				];
			}
			$property = $propertyCheck['content'];
			
			if(!empty($property)) {
				return [
					'success' => false,
					'content' => 'ALREADY_EXISTS' // property already exists in wishlist
				];
			}

			$query = "INSERT INTO wishlist (id_utente, id_proprieta) VALUES (?, ?)";
			$stmt = $this->connection->prepare($query);
			$stmt->bind_param("ii", $idUtente, $idProprieta);
			$stmt->execute();

			$affectedRows = $stmt->affected_rows;
			$stmt->close();

			if ($affectedRows > 0) {
				return [
					'success' => true,
					'content' => null // successfully inserted into wishlist
				];
			} else {
				return [
					'success' => false,
					'content' => 'INSERT_FAILED' // insertion failed
				];
			}
		} catch (\mysqli_sql_exception $e) {
			error_log("Database error in insertToWishlist: " . $e->getMessage());
			return [
				'success' => false,
				'content' => 'DB_ERROR'
			];
		}
	}
}
?>