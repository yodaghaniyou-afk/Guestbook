# Guestbook 📖

Un mini livre d'or en PHP/MySQL, permettant de laisser des messages publics.

## Fonctionnalités
- Ajouter un message (nom + texte) via un formulaire (`$_POST`)
- Rechercher un message par nom via l'URL (`$_GET`)
- Affichage de tous les messages, du plus récent au plus ancien
- Connexion sécurisée à la base via PDO (requêtes préparées, protection contre les injections SQL)
- Architecture orientée objet avec la classe `MessageManager`

## Prérequis
- Un environnement PHP + MySQL (WampServer, XAMPP, ou équivalent)

## Installation

1. Cloner le dépôt dans le dossier `www` de votre serveur local :

git clone https://github.com/yodaghaniyou-afk/Guestbook.git

2. Créer la base de données dans phpMyAdmin :

CREATE DATABASE guestbook_db;

3. Créer la table `messages` :

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

4. Copier `config.example.php` en `config.php`, et renseigner vos identifiants MySQL :

cp config.example.php config.php

5. Lancer le serveur local (WampServer, etc.) et ouvrir dans le navigateur :

http://localhost/Guestbook/

## Structure du projet
- `index.php` — page principale (formulaire, recherche, affichage, logique POO)
- `config.php` — connexion à la base (non versionné, voir `.gitignore`)
- `config.example.php` — modèle de configuration à copier
- `style.css` — mise en forme
- `.gitignore` — exclut les fichiers sensibles du versionnement

## Technologies utilisées
- PHP 8 (POO, PDO)
- MySQL / MariaDB
- HTML / CSS