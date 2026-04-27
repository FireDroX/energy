# Cahier des charges

## Projet Annuel : Application web “Monster Energy Carousel”

## 0. Groupe
* Hassrol
* Adrien

---

## 1. Présentation du projet

### Objectif

Créer une application web permettant de :

* Gérer une collection de boissons **Monster Energy**
* Ajouter / modifier / supprimer des items
* Afficher ces boissons sous forme de **carousel interactif**

---

## 2. Fonctionnalités

### 2.1 Authentification

* Formulaire de connexion (login / mot de passe)
* Vérification via base de données SQL
* Accès sécurisé au panneau admin

---

### 2.2 Panneau d’administration

#### Ajouter un item

Champs :

* Nom
* Description
* Image (URL ou upload (base64))
* Note (sur 10)
* Lieu d’achat

#### Modifier un item

* Pré-remplissage des champs
* Mise à jour en base

#### Supprimer un item

* Bouton de suppression avec confirmation

---

### 2.3 Carousel d’items

* Affichage dynamique des boissons
* Navigation :

  * Boutons précédent / suivant
  * Swipe (optionnel)
* Contenu affiché :

  * Image
  * Nom
  * Description
  * Note
  * Lieu d’achat

---

### 2.4 Consultation publique

* Accès libre au carousel sans login
* Interface responsive

---

## 3. Interface utilisateur (UI/UX)

### Contraintes

* Design moderne (inspiré gaming / énergie)
* Responsive (mobile + desktop)

### Pages principales

* Page accueil (carousel)
* Page login
* Dashboard admin

---

## 4. Architecture technique

### Frontend

Design :

* Figma

Technologies :

* HTML5
* CSS3
* JavaScript (vanilla)

Fonctions :

* Affichage du carousel
* Requêtes API (fetch)

---

### Backend

Technologie :

* PHP :(

Fonctions :

* API REST
* Authentification
* Gestion Create, Read, Update et Delete des items

---

### Base de données

* SQL (MySQL)
* Modèle Conceptuel de Données
* Modèle Logique de Données


## 5. API (Backend)

### Auth

* `POST /login`

---

### Items

* `GET /items` → récupérer tous les items
* `GET /items/:id` → récupérer un item
* `POST /items` → ajouter un item
* `PUT /items/:id` → modifier
* `DELETE /items/:id` → supprimer

---

## 6. Fonctionnement global

1. L’utilisateur arrive sur le site → voit le carousel
2. L’admin se connecte
3. Il accède au dashboard :

   * Ajoute/modifie/supprime des boissons
4. Les données sont stockées en SQL
5. Le frontend récupère les données via API
6. Le carousel se met à jour dynamiquement

---

## 7. Sécurité

* Protection des routes admin (token/session)
* Validation des données (backend)

---

## 8. Contraintes techniques

* Séparation frontend / backend
* Utilisation de Git (versioning)

---

## 9. Livrables

* Code source complet
* Base de données
* Documentation (README)
* Démo fonctionnelle

---

## 10. Mise en prod

* Sur le serv qu'on achetera + nom de domaine
* Sur mon serv local (backup) pa.addrien.fr