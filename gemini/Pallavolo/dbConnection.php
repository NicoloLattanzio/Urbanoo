<?php
namespace DB;

class DBAccess {

	private const HOST_DB = "localhost";
	private const DATABASE_NAME = "ansgreva";
	private const USERNAME = "ansgreva";
	private const PASSWORD = "ku4quahpaij6eiCh";

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


	public function getList() {

		$query = "SELECT * FROM atleti ORDER BY ID ASC";
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

	public function insertNewElement($nome, $capitano, $dataNascita, $luogo, $squadra, $ruolo, $altezza, $maglia, $magliaNazionale, $punti, $riconoscimenti, $note, $genere) {

		
	}

	
}


?>