DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS immagini;
DROP TABLE IF EXISTS proprieta;
DROP TABLE IF EXISTS utenti;

CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    ruolo VARCHAR(20) DEFAULT 'utente',
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crea la tabella proprieta
CREATE TABLE IF NOT EXISTS proprieta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descrizione TEXT,
    citta VARCHAR(100),
    tipologia ENUM('Monolocale', 'Bilocale', 'Trilocale', 'Villa', 'Attico', 'Rustico') NOT NULL,
    prezzo DECIMAL(10,2),
    metri_quadri INT,
    indirizzo VARCHAR(50) NOT NULL UNIQUE,
    locali INT,
    disponibilita BIT,
    immagine VARCHAR(50),
    data_inserimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS immagini (
    id_proprieta INT,
    immagine VARCHAR(50),
    CONSTRAINT PK_IMGs 
        PRIMARY KEY (id_proprieta, immagine),
    CONSTRAINT immagini_ibfk_1 
        FOREIGN KEY (id_proprieta) 
        REFERENCES proprieta(id) 
        ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wishlist (
    id_utente INT,
    id_proprieta INT,
    CONSTRAINT wishlist_fk_utente
        FOREIGN KEY (id_utente)
        REFERENCES utenti(id)
        ON DELETE CASCADE,
    CONSTRAINT wishlist_fk_proprieta
        FOREIGN KEY (id_proprieta)
        REFERENCES proprieta(id)
        ON DELETE CASCADE,
    PRIMARY KEY (id_utente,id_proprieta)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserisci un utente di test (password: admin)
INSERT INTO utenti (nome, cognome, username, email, password, ruolo) VALUES
    (
        'Admin',
        'Test',
        'admin',
        'admin@urbanoo.com',
        '$2y$10$HkJ4MtME/AHmtayp1Ij2QucMxdbBJvSYc8WG69TQEfH27tbpJnLJm', -- hashed password for 'admin'
        'admin'
    ),
    (
        'Utente',
        'Test',
        'user',
        'utente@urbanoo.com',
        '$2y$10$SKBx5/v4XZAKM7v7lN893ue1i8mVkOE9w60rWkVnG3Xa0TKd67Wb6', -- hashed password for 'user'
        'utente'
    );



INSERT INTO proprieta (nome, descrizione, citta, tipologia, prezzo, metri_quadri, indirizzo, locali, disponibilita, immagine) VALUES 
(
    'Attico Vista Duomo', 
    'Splendido attico con terrazzo panoramico nel cuore di Milano.', 
    'Milano', 
    'Attico', 
    1250000.00, 
    150, 
    'Via Torino 12', 
    4, 
    1, 
    '../img/torre.png'
),
(
    'Villa i Cipressi', 
    'Villa indipendente immersa nel verde con piscina privata.', 
    'Firenze', 
    'Villa', 
    850000.00, 
    250, 
    'Via dei Colli 45', 
    6, 
    1, 
    '../img/villa.png'
),
(
    'Bilocale Moderno Navigli', 
    'Appartamento appena ristrutturato, ideale per giovani coppie o investimento.', 
    'Milano', 
    'Bilocale', 
    320000.00, 
    65, 
    'Ripa di Porta Ticinese 5', 
    2, 
    1, 
    '../img/emerald.png'
),
(
    'Rustico Toscano', 
    'Antico casale in pietra da ristrutturare con ampio terreno agricolo.', 
    'Siena', 
    'Rustico', 
    195000.00, 
    180, 
    'Strada Provinciale 22', 
    5, 
    1, 
    '../img/Skyscraper_Rework.png'
),
(
    'Monolocale Centro Storico', 
    'Piccola soluzione abitativa funzionale nel centro di Napoli.', 
    'Napoli', 
    'Monolocale', 
    115000.00, 
    35, 
    'Via Toledo 110', 
    1, 
    1, 
    '../img/costa.png'
);

-- Aggiornamento Wishlist con ID esistenti (assumendo che l'utente 2 esista)
INSERT INTO wishlist(id_utente, id_proprieta) VALUES (2, 1);
INSERT INTO wishlist(id_utente, id_proprieta) VALUES (2, 3);


/*INSERT INTO proprieta (nome, descrizione, citta, tipologia, prezzo, metri_quadri, indirizzo, locali, disponibilita, immagine) VALUES ('casa', 'Casa a caso', 'Napoli', 'Casa napoletana', 10, 55, 'Via napoletana 55', 32, 1,'../img/attici.png');
INSERT INTO proprieta (nome, descrizione, citta, tipologia, prezzo, metri_quadri, indirizzo, locali, disponibilita, immagine) VALUES ('casa gay', 'Casa a caso', 'Vesuvio', 'Casa con cenere', 2, 76,  'Via napoletana 45', 21, 0,'../img/Skyscraper_Rework.png');
INSERT INTO proprieta (nome, descrizione, citta, tipologia, prezzo, metri_quadri, indirizzo, locali, disponibilita, immagine) VALUES ( 'casa 2121321', 'Casa a caso', 'Vesuvio', 'Casa con cenere', 2, 76,  'Via napoletana 43', 21, 0,'../img/Skyscraper_Rework.png');

INSERT INTO wishlist(id_utente, id_proprieta) VALUES (2,1);
INSERT INTO wishlist(id_utente, id_proprieta) VALUES (2,2);*/