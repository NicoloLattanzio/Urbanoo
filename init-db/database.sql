DROP TABLE IF EXISTS utenti;
DROP TABLE IF EXISTS proprieta;


CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    ruolo VARCHAR(20) DEFAULT 'utente',
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ) ENGINE=InnoDB;

-- Crea la tabella proprieta
CREATE TABLE IF NOT EXISTS proprieta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    citta VARCHAR(100),
    tipologia VARCHAR(50),
    prezzo DECIMAL(10,2),
    metri_quadri INT,
    data_inserimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;

-- Inserisci un utente di test (password: password123)
INSERT INTO utenti (nome, cognome, email, username, password, ruolo) VALUES
    (
        'Admin',
        'Test',
        'admin@urbanoo.com',
        'admin',
        'password123',
        'admin'
    );

