-- Tabella Utenti
CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Aumentata per gestire password hashate
    telefono VARCHAR(22),
    ruolo ENUM('user', 'admin') DEFAULT 'user', 
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabella Proprietà (Rinominata per coerenza con il codice PHP)
CREATE TABLE proprieta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL, 
    descrizione TEXT,
    tipologia VARCHAR(50) NOT NULL, 
    indirizzo VARCHAR(150),
    citta VARCHAR(100) NOT NULL,
    prezzo INT NOT NULL,
    metri_quadri INT NOT NULL, 
    locali INT NOT NULL,
    bagni INT DEFAULT 1,
    immagine VARCHAR(255), 
    data_inserimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    disponibile BOOLEAN DEFAULT TRUE,
    agente_id INT,
    FOREIGN KEY (agente_id) REFERENCES utenti(id) ON DELETE SET NULL
);

-- Tabella Messaggi
CREATE TABLE messaggi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mittente_id INT NOT NULL,
    destinatario_id INT NOT NULL,
    immobile_id INT,
    testo TEXT NOT NULL,
    data_invio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mittente_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (immobile_id) REFERENCES proprieta(id) ON DELETE SET NULL
);

-- Indici per ottimizzare le ricerche dei filtri
CREATE INDEX idx_proprieta_citta ON proprieta(citta);
CREATE INDEX idx_proprieta_prezzo ON proprieta(prezzo);
CREATE INDEX idx_proprieta_tipologia ON proprieta(tipologia);