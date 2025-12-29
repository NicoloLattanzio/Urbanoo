CREATE TABLE utenti (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(20) NOT NULL,
    cognome VARCHAR(20) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(30) NOT NULL,
    telefono VARCHAR(22),
    ruolo VARCHAR(5) NOT NULL DEFAULT 'user',
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (ruolo IN ('user', 'admin'))
);

CREATE TABLE immobili (
    id SERIAL PRIMARY KEY,
    titolo VARCHAR(100) NOT NULL,
    descrizione TEXT,
    tipologia VARCHAR(20) NOT NULL,
    CHECK (tipologia IN ('appartamento', 'villa', 'ufficio', 'terreno', 'garage')),
    indirizzo VARCHAR(150) NOT NULL,
    citta VARCHAR(100) NOT NULL,
    prezzo INT NOT NULL,
    CHECK (prezzo > 0),
    metri_quadri INT NOT NULL,
    CHECK (metri_quadri > 0),
    locali INT NOT NULL,
    CHECK (locali > 0),
    immagine_copertina VARCHAR(255),
    data_inserimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    disponibile BOOLEAN NOT NULL DEFAULT TRUE
);


CREATE INDEX idx_immobili_citta ON immobili(citta);
CREATE INDEX idx_immobili_prezzo ON immobili(prezzo);