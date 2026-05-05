CREATE DATABASE IF NOT EXISTS monster;
USE monster;

CREATE TABLE IF NOT EXISTS tags (
    id_tags INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50)
); 

CREATE TABLE IF NOT EXISTS monsters (
    id_monsters INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    description VARCHAR(255),
    image TEXT
);

CREATE TABLE IF NOT EXISTS roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50)
); 

CREATE TABLE IF NOT EXISTS users (
    id_users INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50),
    mail VARCHAR(255),
    mdp VARCHAR(255),
    id_role INT NOT NULL,
    FOREIGN KEY (id_role) REFERENCES roles(id_role)
); 

CREATE TABLE IF NOT EXISTS messages (
    id_messages INT AUTO_INCREMENT PRIMARY KEY,
    contenu VARCHAR(255),
    date_envoie DATETIME DEFAULT CURRENT_TIMESTAMP,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    FOREIGN KEY (sender_id) REFERENCES users(id_users),
    FOREIGN KEY (receiver_id) REFERENCES users(id_users)
);

CREATE TABLE IF NOT EXISTS notes (
    id_rating INT AUTO_INCREMENT PRIMARY KEY,
    note DECIMAL(3,1),
    date_note DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_users INT NOT NULL,
    id_monsters INT NOT NULL,
    FOREIGN KEY (id_users) REFERENCES users(id_users),
    FOREIGN KEY (id_monsters) REFERENCES monsters(id_monsters)
);

CREATE TABLE IF NOT EXISTS commentaires (
    id_commentaires INT AUTO_INCREMENT PRIMARY KEY,
    id_parent INT NULL,
    commentaire VARCHAR(255),
    is_pinned BOOLEAN DEFAULT 0,
    id_monsters INT NOT NULL,
    id_users INT NOT NULL,
    FOREIGN KEY (id_monsters) REFERENCES monsters(id_monsters),
    FOREIGN KEY (id_users) REFERENCES users(id_users)
);

CREATE TABLE IF NOT EXISTS monster_tags (
    id_tags INT,
    id_monsters INT,
    PRIMARY KEY (id_tags, id_monsters),
    FOREIGN KEY (id_tags) REFERENCES tags(id_tags),
    FOREIGN KEY (id_monsters) REFERENCES monsters(id_monsters)
);

CREATE TABLE IF NOT EXISTS likes (
    id_commentaires INT,
    id_users INT,
    date_like DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_commentaires, id_users),
    FOREIGN KEY (id_commentaires) REFERENCES commentaires(id_commentaires),
    FOREIGN KEY (id_users) REFERENCES users(id_users)
);

