CREATE TABLE utenti (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(15) NOT NULL,
    cognome VARCHAR(15) NOT NULL,
    email VARCHAR UNIQUE NOT NULL,
    password VARCHAR(30) NOT NULL,
    telefono VARCHAR(22),
    --ruolo
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE immobili (
    id SERIAL PRIMARY KEY,
    titolo VARCHAR(100) NOT NULL,
    descrizione TEXT,
    agente_id INTEGER REFERENCES utenti(id) ON DELETE SET NULL,
    tipologia  NOT NULL,
    indirizzo VARCHAR(150),
    citta VARCHAR(100) NOT NULL,
    prezzo INT NOT NULL,
    metri_quadri INT NOT NULL,
    locali INT NOT NULL,
    bagni INT DEFAULT 1,
    immagine_copertina VARCHAR(255),
    data_inserimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    disponibile BOOLEAN DEFAULT TRUE
);

CREATE TABLE messaggi (
    id SERIAL PRIMARY KEY,
    mittente_id INTEGER REFERENCES utenti(id) ON DELETE CASCADE NOT NULL,
    destinatario_id INTEGER REFERENCES utenti(id) ON DELETE CASCADE NOT NULL,
    immobile_id INTEGER REFERENCES immobili(id) ON DELETE SET NULL,
    testo TEXT NOT NULL,
);

CREATE INDEX idx_immobili_citta ON immobili(citta);
CREATE INDEX idx_immobili_prezzo ON immobili(prezzo);
CREATE INDEX idx_messaggi_mittente ON messaggi(mittente_id);
CREATE INDEX idx_messaggi_destinatario ON messaggi(destinatario_id);