CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(20) NOT NULL,
    cognome VARCHAR(20) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(22),
    ruolo VARCHAR(5) NOT NULL DEFAULT 'user',
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_ruolo CHECK (ruolo IN ('user', 'admin'))
);

CREATE TABLE proprieta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    tipologia VARCHAR(20) NOT NULL,
    CONSTRAINT chk_tipologia CHECK (tipologia IN ('Monolocale', 'Bilocale', 'Trilocale', 'Villa', 'Attico', 'Rustico')), 
    indirizzo VARCHAR(150) NOT NULL,
    citta VARCHAR(100) NOT NULL,
    prezzo INT NOT NULL,
    CONSTRAINT chk_prezzo CHECK (prezzo > 0),
    metri_quadri INT NOT NULL,
    CONSTRAINT chk_mq CHECK (metri_quadri > 0),
    locali INT NOT NULL,
    CONSTRAINT chk_locali CHECK (locali > 0),
    immagine VARCHAR(255), -- Percorso dell'immagine
    data_inserimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    disponibile BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE INDEX idx_proprieta_citta ON proprieta(citta);
CREATE INDEX idx_proprieta_prezzo ON proprieta(prezzo);