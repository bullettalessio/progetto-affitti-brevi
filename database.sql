-- Creazione del database
DROP DATABASE IF EXISTS affitti_brevi;
CREATE DATABASE affitti_brevi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE affitti_brevi;

-- 1. Tabella Utenti (Host e Ospiti)
CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('host', 'ospite') NOT NULL DEFAULT 'ospite',
    creato_il TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabella Token di Accesso / Sessioni
CREATE TABLE token_accesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    scadenza DATETIME NOT NULL,
    FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Tabella Località
CREATE TABLE localita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    regione VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 4. Tabella Immobili
CREATE TABLE immobili (
    id INT AUTO_INCREMENT PRIMARY KEY,
    host_id INT NOT NULL,
    localita_id INT NOT NULL,
    titolo VARCHAR(150) NOT NULL,
    descrizione TEXT,
    prezzo_notte DECIMAL(10,2) NOT NULL,
    attivo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (host_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (localita_id) REFERENCES localita(id) ON DELETE CASCADE,
    INDEX idx_prezzo (prezzo_notte)
) ENGINE=InnoDB;

-- 5. Tabella Servizi
CREATE TABLE servizi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 6. Tabella Ponte Molti-a-Molti (Immobili <-> Servizi)
CREATE TABLE immobile_servizio (
    immobile_id INT NOT NULL,
    servizio_id INT NOT NULL,
    PRIMARY KEY (immobile_id, servizio_id),
    FOREIGN KEY (immobile_id) REFERENCES immobili(id) ON DELETE CASCADE,
    FOREIGN KEY (servizio_id) REFERENCES servizi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Tabella Prenotazioni
CREATE TABLE prenotazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    immobile_id INT NOT NULL,
    ospite_id INT NOT NULL,
    arrivo DATE NOT NULL,
    partenza DATE NOT NULL,
    num_ospiti INT NOT NULL,
    totale DECIMAL(10,2) NOT NULL,
    stato ENUM('in attesa', 'confermata', 'annullata') DEFAULT 'in attesa',
    creato_il TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (immobile_id) REFERENCES immobili(id) ON DELETE CASCADE,
    FOREIGN KEY (ospite_id) REFERENCES utenti(id) ON DELETE CASCADE,
    INDEX idx_date (arrivo, partenza)
) ENGINE=InnoDB;

-- --- DATI DI PROVA ---

-- Inserimento utenti (Password hashate con password_hash('password123', PASSWORD_DEFAULT))
INSERT INTO utenti (id, nome, email, password, ruolo) VALUES
(1, 'Mario Host', 'host@affitti.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'host'),
(2, 'Luigi Ospite', 'ospite@affitti.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ospite');

-- Inserimento Località
INSERT INTO localita (id, nome, regione) VALUES
(1, 'Roma', 'Lazio'),
(2, 'Milano', 'Lombardia'),
(3, 'Firenze', 'Toscana');

-- Inserimento Servizi
INSERT INTO servizi (id, nome) VALUES
(1, 'Wi-Fi'),
(2, 'Aria Condizionata'),
(3, 'Cucina');

-- Inserimento Immobili
INSERT INTO immobili (id, host_id, localita_id, titolo, descrizione, prezzo_notte, attivo) VALUES
(1, 1, 1, 'Appartamento Colosseo', 'Splendido appartamento vicino al Colosseo', 90.00, TRUE),
(2, 1, 2, 'Monolocale Duomo', 'Moderno monolocale in centro a Milano', 120.00, TRUE);

-- Associazione Servizi agli Immobili (Tabella ponte)
INSERT INTO immobile_servizio (immobile_id, servizio_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 2), (2, 3);

-- Inserimento Prenotazione di prova
INSERT INTO prenotazioni (immobile_id, ospite_id, arrivo, partenza, num_ospiti, totale, stato) VALUES
(1, 2, '2026-06-01', '2026-06-06', 2, 450.00, 'in attesa');
