CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    ruolo VARCHAR(20) DEFAULT 'utente',
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crea la tabella proprieta
CREATE TABLE IF NOT EXISTS proprieta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    citta VARCHAR(100),
    tipologia VARCHAR(50),
    prezzo DECIMAL(10,2),
    metri_quadri INT,
    indirizzo VARCHAR(50),
    locali INT,
    disponibilita BIT,
    immagine VARCHAR(30),
    data_inserimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserisci un utente di test (password: password123)
INSERT INTO utenti (nome, cognome, email, password, ruolo) VALUES
    (
        'Admin',
        'Test',
        'admin@urbanoo.com',
        '$2y$10$6lnIBHp6loYxZap2bQ6bnOJ57zOvcsvZd.BJer8ZSv1y1V3sTaR3.',
        'admin'
    );
INSERT INTO proprieta (id, nome, descrizione, citta, tipologia, prezzo, metri_quadri, indirizzo, locali, disponibilita, immagine) VALUES (1, 'casa', 'Casa a caso', 'Napoli', 'Casa napoletana', 10, 55, 'Via napoletana', 32, 1,'../img/attici.png');
INSERT INTO proprieta (id, nome, descrizione, citta, tipologia, prezzo, metri_quadri, indirizzo, locali, disponibilita, immagine) VALUES (2, 'casa gay', 'Casa a caso', 'Vesuvio', 'Casa con cenere', 2, 76,  'Via napoletana', 21, 0,'../img/Skyscraper_Rework.png');
INSERT INTO proprieta (id, nome, descrizione, citta, tipologia, prezzo, metri_quadri, indirizzo, locali, disponibilita, immagine) VALUES (3, 'casa 2121321', 'Casa a caso', 'Vesuvio', 'Casa con cenere', 2, 76,  'Via napoletana', 21, 0,'../img/Skyscraper_Rework.png');

