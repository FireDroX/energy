# Cahier des charges

## Projet Annuel : Application web “Monster Energy Carousel”

## 0. Groupe

- Hassrol
- Adrien

---

## 1. Présentation du projet

### Objectif

Créer une application web permettant de :

- Gérer une collection de boissons **Monster Energy**
  - Affichage des 3 meilleures boissons sur la page principale avec leur note, visuel et description
  - Page complète regroupant toutes les boissons existantes
    - Carrousel par tags
    - Barre de recherche par nom

- Ajouter / modifier / supprimer des items
- Afficher les boissons sous forme de **carousel interactif**
- Gestion des utilisateurs permettant :
  - Envoyer des commentaires
  - Répondre (reply)
  - Transférer des messages
  - Ajouter des réactions
  - Épingler des messages (admins)
  - Liker et noter les boissons (note globale sur 10 calculée à partir de toutes les notes)

- Gestion de messages en temps semi-réel avec modération
  - Envoi de GIFs (Tenor), avec barre de recherche de GIFs
  - Réactions sur les messages

- Filtrage des boissons avec des tags :
  Original, Collaboration, Juice, Ultra, Coffee, Java, Espresso, Rehab, Reserve, Dragon Tea, Muscle, Nitro, Maxx, Extra Strength, Nitrous, Punch, Dub Edition, Hydro Series, Mutant Series, Beast
- Page des mentions légales
- Différents rôles :
  - users (aucune permission spécifique)
  - contributors (accès complet aux boissons mais pas à la modération des messages)
  - admins (accès complet + modération)

---

## 2. Fonctionnalités

### 2.1 Authentification

- Formulaire de connexion (login / mot de passe)
- Vérification via base de données SQL
- Gestion des sessions ou tokens
- Accès sécurisé aux routes admin et contributor

---

### 2.2 Gestion des boissons (CRUD)

#### Ajouter une boisson

Champs :

- Nom
- Description
- Image (URL)

#### Modifier une boisson

- Mise à jour en base de données

#### Supprimer une boisson

- Bouton de suppression avec confirmation

---

### 2.3 Page principale (Home)

- Affichage des **3 a 5 meilleures boissons**
  - Basé sur la note globale (/10)

- Contenu affiché :
  - Image
  - Nom
  - Description
  - Note

---

### 2.4 Page catalogue

- Affichage de toutes les boissons
- Organisation via :
  - Carousel interactif
  - Filtrage par tags

- Barre de recherche par nom

---

### 2.5 Carousel interactif

- Navigation :
  - Boutons précédent / suivant
  - Swipe (optionnel)

- Affichage :
  - Image
  - Nom
  - Description
  - Note

---

### 2.6 Système utilisateur et interactions

- Gestion des rôles :
  - users
  - contributors
  - admins

- Interactions :
  - Commentaires sur boissons
  - Réponses (reply)
  - Likes
  - Réactions
  - Épinglage de messages (admins)
  - Notes utilisateur sur boissons

---

### 2.7 Chat semi temps réel

- Envoi / réception de messages
- Réponses à des messages
- Réactions sur messages
- Envoi de GIFs via API Tenor
- Recherche de GIFs
- Modération des messages (admins)

---

### 2.8 Pages annexes

- Page des mentions légales

---

## 3. Interface utilisateur (UI/UX)

### Contraintes

- Interface responsive (mobile / desktop)
- Navigation fluide entre :
  - Home
  - Catalogue
  - Chat

### Pages principales

- Page accueil (top 3 + accès carousel)
- Page catalogue (toutes les boissons)
- Page login
- Dashboard admin

---

## 4. Architecture technique

### Frontend

Technologies :

- HTML5
- CSS3
- JavaScript / PHP

Fonctions :

- Affichage du carousel
- Affichage des top 3 boissons
- Gestion des filtres et recherche
- Communication API (fetch)

---

### Backend

Technologie :

- PHP

Fonctions :

- API REST
- Authentification
- Gestion CRUD des boissons
- Gestion utilisateurs et rôles
- Gestion chat (messages, réactions, GIFs)

---

### Base de données

- SQL (MySQL)
- Modèle Conceptuel de Données (MCD)
- Modèle Logique de Données (MLD)

---

## 5. API (Backend)

### Auth

- `POST /login`

---

### Items (boissons)

- `GET /items` → récupérer toutes les boissons
- `GET /items/:id` → récupérer une boisson
- `POST /items` → ajouter une boisson
- `PUT /items/:id` → modifier une boisson
- `DELETE /items/:id` → supprimer une boisson

---

### Interactions

- `POST /items/:id/comment` → ajouter un commentaire
- `POST /comment/:id/reply` → répondre à un commentaire
- `POST /comment/:id/react` → réaction
- `POST /items/:id/rate` → noter une boisson

---

### Chat

- `GET /messages`
- `POST /messages`
- `POST /messages/:id/reply`
- `POST /messages/:id/react`
- `DELETE /messages/:id` (admin)
- Intégration GIF via API Tenor

---

## 6. Fonctionnement global

1. L’utilisateur arrive sur le site et voit les 3 meilleures boissons
2. Il peut accéder au catalogue complet via carousel et recherche
3. Les utilisateurs interagissent via commentaires, likes et chat
4. Les contributors et admins gèrent les boissons
5. Les admins modèrent les messages et interactions
6. Toutes les données sont stockées en base SQL
7. Le frontend consomme l’API REST pour afficher les données dynamiques

---

## 7. Sécurité

- Protection des routes via session ou token
- Vérification des rôles (users / contributors / admins)
- Validation des données côté backend
- Protection des actions sensibles (delete, moderation)

---

## 8. Contraintes techniques

- Séparation frontend / backend
- Utilisation de Git pour le versioning
  - dépôt : [https://github.com/FireDroX/energy](https://github.com/FireDroX/energy)

---

## 9. Livrables

- Code source complet
- Base de données SQL
- Documentation technique (README)
- MCD / MLD
- Démonstration fonctionnelle

---

## 10. Mise en production

- Déploiement sur serveur avec nom de domaine
- Hébergement principal (serveur acheté)
- Serveur local de backup : pa.addrien.fr
