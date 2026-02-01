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
    prezzo INT,
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
    1250000, 
    150, 
    'Via Torino 12', 
    4, 
    1, 
    '../img/attico1.png'
),
(
    'Villa i Cipressi', 
    'Villa indipendente immersa nel verde con piscina privata.', 
    'Firenze', 
    'Villa', 
    850000, 
    250,
    'Via dei Colli 45', 
    6, 
    1, 
    '../img/villa1.png'
),
(
    'Bilocale Moderno Navigli', 
    'Appartamento appena ristrutturato, ideale per giovani coppie o investimento.', 
    'Milano', 
    'Bilocale', 
    320000, 
    65, 
    'Ripa di Porta Ticinese 5', 
    2, 
    1, 
    '../img/bilocale1.png'
),
(
    'Rustico Toscano', 
    'Antico casale in pietra da ristrutturare con ampio terreno agricolo.', 
    'Siena', 
    'Rustico', 
    195000, 
    180, 
    'Strada Provinciale 22', 
    5, 
    1, 
    '../img/rustico1.png'
),
(
    'Monolocale Centro Storico', 
    'Piccola soluzione abitativa funzionale nel centro di Napoli.', 
    'Napoli', 
    'Monolocale', 
    115000, 
    35, 
    'Via Toledo 110', 
    1, 
    1, 
    '../img/monolocale1.png'
),
('Trilocale Prati Elegante', 'Appartamento signorile a pochi passi dal Vaticano, finiture di pregio.', 'Roma', 'Trilocale', 580000, 95, 'Via Cola di Rienzo 24', 3, 1, '../img/trilocale1.png'),
('Casale degli Ulivi', 'Incantevole rustico immerso nelle colline umbre con vista sulla valle.', 'Perugia', 'Rustico', 450000, 220, 'Strada delle Vigne 15', 7, 1, '../img/rustico2.png'),
('Loft Industriale Torino', 'Ampio loft dal design moderno in zona Lingotto, soffitti alti e mattoni a vista.', 'Torino', 'Trilocale', 310000, 110, 'Via Nizza 150', 3, 1, '../img/trilocale2.png'),
('Villa Laguna Lido', 'Esclusiva villa con accesso privato alla spiaggia e giardino mediterraneo.', 'Venezia', 'Villa', 1400000, 320, 'Lungomare Marconi 8', 8, 1, '../img/villa2.png'),
('Bilocale Portici Bologna', 'Grazioso appartamento sotto i portici storici, ideale come pied-à-terre.', 'Bologna', 'Bilocale', 245000, 55, 'Via Zamboni 12', 2, 1, '../img/bilocale2.png'),
('Attico Sole e Mare', 'Attico con tripla esposizione e vista mozzafiato sul Golfo di Palermo.', 'Palermo', 'Attico', 410000, 130, 'Via della Libertà 88', 5, 1, '../img/attico2.png'),
('Trilocale Arena', 'Appartamento storico con affacci diretti sull Arena di Verona.', 'Verona', 'Trilocale', 490000, 85, 'Piazza Bra 3', 3, 1, '../img/trilocale3.png'),
('Monolocale Smart Design', 'Soluzione tecnologica e funzionale nel quartiere Isola.', 'Milano', 'Monolocale', 215000, 32, 'Via Borsieri 7', 1, 1, '../img/monolocale2.png'),
('Villa Vista Mare Genova', 'Proprietà di prestigio nel quartiere Albaro con ampie vetrate sul mare.', 'Genova', 'Villa', 980000, 210, 'Via Giordano Bruno 2', 7, 1, '../img/villa3.png'),
('Rustico Langhe Heritage', 'Casale piemontese circondato dai vigneti del Barolo, patrimonio UNESCO.', 'Cuneo', 'Rustico', 280000, 160, 'Frazione Annunziata 44', 6, 1, '../img/rustico3.png');

-- Aggiornamento Wishlist con ID esistenti (assumendo che l'utente 2 esista)
INSERT INTO wishlist(id_utente, id_proprieta) VALUES (2, 1);
INSERT INTO wishlist(id_utente, id_proprieta) VALUES (2, 3);

-- Foto extra per ogni bilocale (sono uguali per tutti i bilocali inseriti)
INSERT INTO immagini (id_proprieta, immagine) VALUES 
(3, '../img/b1.png'), (3, '../img/b2.png'), (3, '../img/b3.png'), (3, '../img/b4.png'),
(10, '../img/b1.png'), (10, '../img/b2.png'), (10, '../img/b3.png'), (10, '../img/b4.png');

-- Foto extra per ogni monolocale (sono uguali per tutti i monolocali inseriti)
INSERT INTO immagini (id_proprieta, immagine) VALUES 
(5, '../img/m1.png'), (5, '../img/m2.png'), (5, '../img/m3.png'), (5, '../img/m4.png'),
(13, '../img/m1.png'), (13, '../img/m2.png'), (13, '../img/m3.png'), (13, '../img/m4.png');

-- Foto extra per ogni villa (sono uguali per tutte le ville inserite)
INSERT INTO immagini (id_proprieta, immagine) VALUES 
(2, '../img/v1.png'), (2, '../img/v2.png'), (2, '../img/v3.png'), (2, '../img/v4.png'),
(9, '../img/v1.png'), (9, '../img/v2.png'), (9, '../img/v3.png'), (9, '../img/v4.png'),
(14, '../img/v1.png'), (14, '../img/v2.png'), (14, '../img/v3.png'), (14, '../img/v4.png');

-- Foto extra per ogni attico (sono uguali per tutti gli attici inseriti)
INSERT INTO immagini (id_proprieta, immagine) VALUES 
(1, '../img/a1.png'), (1, '../img/a2.png'), (1, '../img/a3.png'), (1, '../img/a4.png'),
(11, '../img/a1.png'), (11, '../img/a2.png'), (11, '../img/a3.png'), (11, '../img/a4.png');

-- Foto extra per ogni trilocale (sono uguali per tutti i trilocali inseriti)
INSERT INTO immagini (id_proprieta, immagine) VALUES 
(6, '../img/t1.png'), (6, '../img/t2.png'), (6, '../img/t3.png'), (6, '../img/t4.png'),
(12, '../img/t1.png'), (12, '../img/t2.png'), (12, '../img/t3.png'), (12, '../img/t4.png'),
(8, '../img/t1.png'), (8, '../img/t2.png'), (8, '../img/t3.png'), (8, '../img/t4.png');

-- Foto extra per ogni rustico (sono uguali per tutti i rustici inseriti)
INSERT INTO immagini (id_proprieta, immagine) VALUES 
(4, '../img/r1.png'), (4, '../img/r2.png'), (4, '../img/r3.png'), (4, '../img/r4.png'),
(7, '../img/r1.png'), (7, '../img/r2.png'), (7, '../img/r3.png'), (7, '../img/r4.png'),
(15, '../img/r1.png'), (15, '../img/r2.png'), (15, '../img/r3.png'), (15, '../img/r4.png');