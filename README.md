# 🎵 Pick My Vinyl
 
**Réservez vos vinyles en ligne, récupérez-les en magasin (Click & Collect).**
 
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/Symfony-7.2-000000?logo=symfony&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/licence-MIT-green)
 
Pick My Vinyl est une application web développée avec **Symfony 7.2**. Elle permet de parcourir un catalogue de vinyles alimenté par l'API [Discogs](https://www.discogs.com/developers), de réserver les disques disponibles chez des enseignes partenaires, puis de venir les retirer sur place.
 
<!-- Ajoutez ici une capture d'écran ou un GIF de démonstration -->
<!-- ![Aperçu](docs/images/preview.png) -->
 
---
 
## 📋 Sommaire
 
- [Fonctionnalités](#-fonctionnalités)
- [Stack technique](#️-stack-technique)
- [Démarrage rapide](#-démarrage-rapide)
- [Configuration](#️-configuration)
- [Utilisation](#-utilisation)
- [Rôles et permissions](#-rôles-et-permissions)
- [Cycle de vie d'une réservation](#-cycle-de-vie-dune-réservation)
- [Intégration Discogs](#-intégration-discogs)
- [Emails automatiques](#-emails-automatiques)
- [Structure du projet](#-structure-du-projet)
- [Personnalisation](#-personnalisation)
- [Tests](#-tests)
- [Commandes utiles](#-commandes-utiles)
- [Dépannage](#-dépannage)
- [Contribuer](#-contribuer)
- [Licence](#-licence)
---
 
## ✨ Fonctionnalités
 
### 👤 Utilisateurs (`ROLE_USER`)
 
- Inscription avec **vérification d'email obligatoire**
- **Recherche** de vinyles via Discogs, avec filtres et pagination
- **Panier** multi-vinyles
- **Réservation** dans un magasin donné, avec email de confirmation
- **Historique** des réservations et **annulation** tant qu'elles sont en attente
### 🏪 Enseignes (`ROLE_STORE`)
 
- Dashboard avec statistiques
- Gestion des stocks du magasin
- Import de vinyles depuis Discogs
- **Validation** ou **rejet** des réservations en attente
- Consultation des réservations du magasin
### 🔐 Administrateurs (`ROLE_ADMIN`)
 
- Dashboard **EasyAdmin** avec statistiques globales (utilisateurs, vinyles, réservations)
- CRUD complet : utilisateurs, vinyles, magasins, réservations (avec filtres)
---
 
## 🛠️ Stack technique
 
| Couche | Technologies |
|---|---|
| Backend | PHP 8.2+, Symfony 7.2, Doctrine ORM, Symfony Mailer, Twig |
| Administration | EasyAdmin 4 |
| Frontend | HTML5, CSS3, JavaScript (vanilla), Font Awesome 6, Google Fonts |
| Intégration | API Discogs via [Calliostro Discogs Bundle](https://github.com/calliostro/discogs-bundle) |
| Base de données | MySQL 8.0+ / MariaDB |
 
---
 
## 🚀 Démarrage rapide
 
### Prérequis
 
- PHP **8.2+** et [Composer](https://getcomposer.org/)
- MySQL **8.0+** ou MariaDB
- [Symfony CLI](https://symfony.com/download) (recommandé)
- Node.js et npm *(uniquement si AssetMapper est utilisé)*
- Un [token Discogs](https://www.discogs.com/settings/developers)
### Installation
 
```bash
# 1. Cloner le projet
git clone https://github.com/votre-username/pick-my-vinyl.git
cd pick-my-vinyl
 
# 2. Installer les dépendances
composer install
npm install   # si AssetMapper est utilisé
 
# 3. Configurer l'environnement (voir section Configuration)
cp .env .env.local
 
# 4. Créer la base et appliquer les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
 
# 5. (Optionnel) Charger les données de démonstration
php bin/console doctrine:fixtures:load
 
# 6. Lancer le serveur
symfony server:start
# ou : php -S localhost:8000 -t public
```
 
L'application est alors disponible sur :
 
| Page | URL |
|---|---|
| Site | `http://localhost:8000` |
| Administration | `http://localhost:8000/admin` |
 
---
 
## ⚙️ Configuration
 
Toutes les variables sensibles se placent dans **`.env.local`** (non versionné — ne le commitez jamais).
 
```env
# Base de données
DATABASE_URL="mysql://user:password@127.0.0.1:3306/pickmyvinyl?serverVersion=8.0"
 
# API Discogs
DISCOGS_TOKEN="votre_token_discogs"
 
# Emails (production)
MAILER_DSN=smtp://user:pass@smtp.example.com:587
 
# Emails (développement, avec MailCatcher/Mailpit)
# MAILER_DSN=smtp://localhost:1025
```
 
Configuration du bundle Discogs, dans `config/packages/calliostro_discogs.yaml` :
 
```yaml
calliostro_discogs:
    token: '%env(DISCOGS_TOKEN)%'
```
 
---
 
## 📖 Utilisation
 
### Créer un compte utilisateur
 
1. Rendez-vous sur `/inscription/user` et remplissez le formulaire.
2. Cliquez sur le lien reçu par email pour **vérifier votre adresse**.
3. Connectez-vous sur `/login`.
### Créer un compte enseigne
 
1. Rendez-vous sur `/inscription/boutique` et renseignez les informations du magasin.
2. **Vérifiez votre email**.
3. Accédez au dashboard gérant.
### Créer un compte administrateur
 
```bash
php bin/console app:create-admin admin@example.com <mot_de_passe>
```
 
> 💡 Un mot de passe saisi en ligne de commande reste dans l'historique du shell. Utilisez un mot de passe temporaire et changez-le après la première connexion.
 
Alternative : ajouter manuellement `ROLE_ADMIN` aux rôles de l'utilisateur en base.
 
---
 
## 🔐 Rôles et permissions
 
| Fonctionnalité | `ROLE_USER` | `ROLE_STORE` | `ROLE_ADMIN` |
|---|:---:|:---:|:---:|
| Consulter le catalogue | ✅ | ✅ | ✅ |
| Créer une réservation | ✅ | ❌ | ❌ |
| Annuler une réservation | ✅ | ❌ | ❌ |
| Gérer les stocks du magasin | ❌ | ✅ | ❌ |
| Valider / rejeter des réservations | ❌ | ✅ | ❌ |
| CRUD vinyles | ❌ | ❌ | ✅ |
| CRUD magasins | ❌ | ❌ | ✅ |
| CRUD utilisateurs | ❌ | ❌ | ✅ |
| Dashboard admin | ❌ | ❌ | ✅ |
 
---
 
## 🔄 Cycle de vie d'une réservation
 
```mermaid
stateDiagram-v2
    [*] --> pending : Création par le client
    pending --> confirmed : Validation par le gérant
    pending --> rejected : Rejet par le gérant
    pending --> cancelled : Annulation par le client
    confirmed --> [*]
    rejected --> [*]
    cancelled --> [*]
```
 
| Statut | Description | Modifiable par |
|---|---|---|
| `pending` | En attente de validation | Gérant (validation/rejet), client (annulation) |
| `confirmed` | Validée par le gérant | — |
| `rejected` | Rejetée par le gérant | — |
| `cancelled` | Annulée par le client | — |
 
Le client dispose de **7 jours** pour retirer sa réservation en magasin.
 
---
 
## 🎵 Intégration Discogs
 
L'application interroge l'API Discogs pour la recherche et les métadonnées (titre, artiste, année, pochette).
 
| Usage | Endpoint Discogs |
|---|---|
| Recherche (`/catalogue?q=...`) | `GET /database/search` |
| Détail d'une édition | `GET /releases/{id}` |
 
- **Pagination** : 50 résultats par page
- **Authentification** : token personnel requis
- **Rate limit** : 60 requêtes/minute en mode authentifié — voir la [documentation officielle](https://www.discogs.com/developers) pour les limites à jour
---
 
## 📧 Emails automatiques
 
| Email | Déclencheur | Contenu |
|---|---|---|
| Vérification | Inscription | Lien d'activation du compte (valable 24 h) |
| Confirmation de réservation | Création d'une réservation | Détail de la réservation, magasin, délai de retrait de 7 jours |
 
---
 
## 📁 Structure du projet
 
```
pick-my-vinyl/
├── assets/                 # JS et CSS
├── bin/                    # Console Symfony
├── config/                 # Configuration (packages, routes)
├── migrations/             # Migrations Doctrine
├── public/                 # Point d'entrée web et images statiques
├── src/
│   ├── Controller/
│   │   ├── Admin/          # Contrôleurs EasyAdmin
│   │   ├── CartController.php
│   │   ├── CatalogController.php
│   │   ├── ReservationController.php
│   │   └── StoreManagerController.php
│   ├── Entity/             # User, Vinyl, Store, Reservation, Stock
│   ├── Form/               # Formulaires Symfony
│   ├── Repository/         # Repositories Doctrine
│   ├── Security/           # Authenticator
│   └── Service/            # CartService, EmailService, ReservationService
├── templates/              # Templates Twig (admin, cart, catalog, emails,
│                           #   registration, reservation, security, store_manager)
└── var/                    # Cache et logs
```
 
---
 
## 🎨 Personnalisation
 
**Couleurs du thème**, dans `assets/styles/app.css` :
 
```css
:root {
    --bordeaux: #3D0D0D;
    --bordeaux-light: #631D22;
    --cream: #fcfbf9;
    --gold: #D4AF37;
}
```
 
**Logo et favicon** : remplacez `public/images/pickmyvinyl.png` (et/ou `public/favicon.ico`).
 
---
 
## 🧪 Tests
 
```bash
# Tous les tests
php bin/phpunit
 
# Tests fonctionnels uniquement
php bin/phpunit --testsuite functional
```
 
---
 
## 📝 Commandes utiles
 
| Action | Commande |
|---|---|
| Créer une migration | `php bin/console make:migration` |
| Appliquer les migrations | `php bin/console doctrine:migrations:migrate` |
| Vider le cache | `php bin/console cache:clear` |
| Créer un admin | `php bin/console app:create-admin <email> <mot_de_passe>` |
| Lancer le serveur | `symfony server:start` |
 
---
 
## 🐛 Dépannage
 
| Problème | Solution |
|---|---|
| Manque de mémoire PHP | `php -d memory_limit=512M bin/console cache:clear` |
| Erreur de token CSRF | Vérifiez que le champ `_token` est bien généré dans le formulaire concerné. Ne désactivez pas la protection CSRF en production. |
| Emails non envoyés | Vérifiez `MAILER_DSN` dans `.env.local` (en dev, lancez MailCatcher ou Mailpit). |
| L'API Discogs ne répond pas | Vérifiez `DISCOGS_TOKEN` dans `.env.local` et que la limite de requêtes n'est pas atteinte. |
 
---
 
## 🤝 Contribuer
 
Les contributions sont les bienvenues !
 
1. Forkez le dépôt
2. Créez une branche : `git checkout -b feature/ma-fonctionnalite`
3. Commitez vos changements : `git commit -m "Ajoute ma fonctionnalité"`
4. Poussez la branche : `git push origin feature/ma-fonctionnalite`
5. Ouvrez une Pull Request
Merci de lancer `php bin/phpunit` avant toute PR.
 
---
 
## 📄 Licence
 
Distribué sous licence **MIT**. Voir le fichier [`LICENSE`](LICENSE).
 
---
 
## 👥 Auteurs et contact
 
- **Développeur principal** : Votre Nom — projet pédagogique, formation Développement Web
- **Contact** : contact@pickmyvinyl.com
- **GitHub** : https://github.com/votre-username/pick-my-vinyl
## 🙏 Remerciements
 
[Discogs](https://www.discogs.com) pour le catalogue, la communauté [Symfony](https://symfony.com) et [EasyAdmin](https://symfony.com/bundles/EasyAdminBundle/current/index.html), [Font Awesome](https://fontawesome.com) et [Google Fonts](https://fonts.google.com).
 
---
 
<p align="center">Développé avec ❤️ et 🎵 par l'équipe Pick My Vinyl</p>
