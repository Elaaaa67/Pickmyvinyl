# 🎵 Pick My Vinyl

**Application web de réservation de vinyles en magasin (Click & Collect)**

Une plateforme moderne permettant aux utilisateurs de consulter un catalogue de vinyles, de réserver des vinyles disponibles dans des enseignes partenaires et de venir les récupérer sur place.

---

## 📋 Table des matières

- [Présentation](#présentation)
- [Fonctionnalités](#fonctionnalités)
- [Technologies](#technologies)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Structure du projet](#structure-du-projet)
- [Rôles et permissions](#rôles-et-permissions)
- [API Discogs](#api-discogs)
- [Screenshots](#screenshots)
- [Contribuer](#contribuer)
- [Licence](#licence)

---

## 🎯 Présentation

Pick My Vinyl est une application web développée avec **Symfony 7.2** qui permet de gérer un système de réservation de vinyles en magasin. Les utilisateurs peuvent :

- 🔍 Rechercher des vinyles via l'API Discogs
- 🛒 Ajouter des vinyles à leur panier
- 📋 Créer des réservations dans des magasins spécifiques
- ✅ Recevoir des confirmations par email
- 🏪 (Pour les gérants) Gérer les stocks et valider les réservations

---

## ✨ Fonctionnalités

### 👤 Pour les utilisateurs (ROLE_USER)

- ✅ **Inscription avec vérification d'email obligatoire**
- 🔍 **Recherche de vinyles** via l'API Discogs
- 📖 **Consultation du catalogue** avec filtres et pagination
- 🛒 **Panier d'achat** pour sélectionner plusieurs vinyles
- 📋 **Création de réservations** dans des magasins spécifiques
- 📧 **Emails de confirmation** pour chaque réservation
- 📅 **Consultation de l'historique** des réservations
- ❌ **Annulation de réservations** en attente

### 🏪 Pour les enseignes (ROLE_STORE)

- 📊 **Dashboard gérant** avec statistiques
- 📦 **Gestion des stocks** du magasin
- 🔍 **Import de vinyles** depuis Discogs
- ✅ **Validation des réservations** en attente
- ❌ **Rejet des réservations** impossibles à honorer
- 📋 **Consultation des réservations** de son magasin

### 🔐 Pour les administrateurs (ROLE_ADMIN)

- 🎨 **Dashboard EasyAdmin** avec statistiques globales
- 👥 **Gestion des utilisateurs** (CRUD complet)
- 🎵 **Gestion des vinyles** (CRUD complet)
- 🏪 **Gestion des magasins** (CRUD complet)
- 📋 **Gestion des réservations** (CRUD complet avec filtres)
- 📊 **Statistiques en temps réel** (utilisateurs, vinyls, réservations)

---

## 🛠️ Technologies

### Backend
- **PHP 8.2+**
- **Symfony 7.2** (Framework MVC)
- **Doctrine ORM** (Base de données)
- **EasyAdmin 4** (Interface d'administration)
- **Symfony Mailer** (Envoi d'emails)
- **Twig** (Moteur de templates)

### Frontend
- **HTML5 / CSS3**
- **JavaScript (Vanilla)**
- **Font Awesome 6** (Icônes)
- **Google Fonts** (Typographie)

### Intégrations
- **API Discogs** (Catalogue de vinyles)
- **Calliostro Discogs Bundle** (Client PHP)

### Base de données
- **MySQL 8.0** / **MariaDB**

---

## 📦 Installation

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL 8.0+ ou MariaDB
- Node.js et npm (optionnel pour AssetMapper)

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone https://github.com/votre-username/pick-my-vinyl.git
cd pick-my-vinyl
```

2. **Installer les dépendances**
```bash
composer install
npm install  # Si AssetMapper est utilisé
```

3. **Configurer la base de données**

Créer un fichier `.env.local` :
```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/pickmyvinyl?serverVersion=8.0"
```

4. **Créer la base de données**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Charger les fixtures (optionnel)**
```bash
php bin/console doctrine:fixtures:load
```

6. **Configurer l'API Discogs**

Ajouter dans `.env.local` :
```env
DISCOGS_TOKEN="votre_token_discogs"
```

Obtenir un token : https://www.discogs.com/settings/developers

7. **Lancer le serveur**
```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

8. **Accéder à l'application**
- Site principal : `http://localhost:8000`
- Dashboard admin : `http://localhost:8000/admin`

---

## ⚙️ Configuration

### Configuration des emails

Dans `.env.local` :
```env
MAILER_DSN=smtp://user:pass@smtp.example.com:587
```

Pour le développement (MailCatcher) :
```env
MAILER_DSN=smtp://localhost:1025
```

### Configuration Discogs

Dans `config/packages/calliostro_discogs.yaml` :
```yaml
calliostro_discogs:
    token: '%env(DISCOGS_TOKEN)%'
```

---

## 🚀 Utilisation

### Créer un compte utilisateur

1. Aller sur `/inscription/user`
2. Remplir le formulaire
3. **Vérifier son email** (lien envoyé automatiquement)
4. Se connecter sur `/login`

### Créer un compte enseigne

1. Aller sur `/inscription/boutique`
2. Remplir les informations du magasin
3. **Vérifier son email**
4. Accéder au dashboard gérant

### Créer un compte administrateur

Via la console :
```bash
php bin/console app:create-admin admin@example.com password
```

Ou manuellement dans la base de données en ajoutant `ROLE_ADMIN` aux rôles.

---

## 📁 Structure du projet

```
pick-my-vinyl/
├── assets/               # Assets front-end (JS, CSS)
├── bin/                  # Scripts console
├── config/               # Configuration Symfony
│   ├── packages/         # Configuration des bundles
│   └── routes/           # Configuration des routes
├── migrations/           # Migrations Doctrine
├── public/               # Point d'entrée web
│   └── images/           # Images statiques
├── src/
│   ├── Controller/       # Contrôleurs MVC
│   │   ├── Admin/        # Controllers EasyAdmin
│   │   ├── CartController.php
│   │   ├── CatalogController.php
│   │   ├── ReservationController.php
│   │   └── StoreManagerController.php
│   ├── Entity/           # Entités Doctrine
│   │   ├── User.php
│   │   ├── Vinyl.php
│   │   ├── Store.php
│   │   ├── Reservation.php
│   │   └── Stock.php
│   ├── Form/             # Formulaires Symfony
│   ├── Repository/       # Repositories Doctrine
│   ├── Service/          # Services métier
│   │   ├── CartService.php
│   │   ├── EmailService.php
│   │   └── ReservationService.php
│   └── Security/         # Authenticator
├── templates/            # Templates Twig
│   ├── admin/            # Templates EasyAdmin
│   ├── cart/             # Templates panier
│   ├── catalog/          # Templates catalogue
│   ├── emails/           # Templates emails
│   ├── registration/     # Templates inscription
│   ├── reservation/      # Templates réservations
│   ├── security/         # Templates connexion
│   ├── store_manager/    # Templates gérant
│   └── base.html.twig    # Layout principal
└── var/                  # Cache et logs
```

---

## 🔐 Rôles et permissions

| Fonctionnalité | ROLE_USER | ROLE_STORE | ROLE_ADMIN |
|----------------|:---------:|:----------:|:----------:|
| Consulter catalogue | ✅ | ✅ | ✅ |
| Créer réservation | ✅ | ❌ | ❌ |
| Annuler réservation | ✅ | ❌ | ❌ |
| Gérer stocks magasin | ❌ | ✅ | ❌ |
| Valider réservations | ❌ | ✅ | ❌ |
| CRUD Vinyles | ❌ | ❌ | ✅ |
| CRUD Magasins | ❌ | ❌ | ✅ |
| CRUD Utilisateurs | ❌ | ❌ | ✅ |
| Dashboard Admin | ❌ | ❌ | ✅ |

---

## 🎵 API Discogs

L'application utilise l'API Discogs pour :

- **Recherche de vinyles** : `/catalogue?q=recherche`
- **Détails d'un vinyle** : Récupération des métadonnées (titre, artiste, année, pochette)
- **Pagination** : 50 résultats par page

### Endpoints utilisés

- `GET /database/search` - Recherche générale
- `GET /releases/{id}` - Détails d'un release

### Limitations

- **Authentification** : Token personnel requis
- **Rate limit** : 60 requêtes/minute
- **Quota** : 10 000 requêtes/24h

---

## 📊 Statuts des réservations

| Statut | Description | Modifiable par |
|--------|-------------|----------------|
| `pending` | En attente de validation | Gérant (validation/rejet) |
| `confirmed` | Validée par le gérant | - |
| `cancelled` | Annulée par le client | Client (si pending) |
| `rejected` | Rejetée par le gérant | Gérant |

---

## 📧 Emails automatiques

### Email de vérification
- **Envoyé à** : Inscription
- **Contenu** : Lien de vérification (valable 24h)
- **Action** : Activer le compte

### Email de confirmation de réservation
- **Envoyé à** : Création de réservation
- **Contenu** : Détails réservation, magasin, délai 7 jours
- **Action** : Informer l'utilisateur

---

## 🎨 Personnalisation

### Couleurs du thème

Dans `assets/styles/app.css` :
```css
:root {
    --bordeaux: #3D0D0D;
    --bordeaux-light: #631D22;
    --cream: #fcfbf9;
    --gold: #D4AF37;
}
```

### Logo

Remplacer `public/images/pickmyvinyl.png`

### Favicon

Remplacer `public/images/pickmyvinyl.png` ou `public/favicon.ico`

---

## 🧪 Tests

```bash
# Tests unitaires
php bin/phpunit

# Tests fonctionnels
php bin/phpunit --testsuite functional
```

---

## 📝 Commandes utiles

```bash
# Créer une migration
php bin/console make:migration

# Appliquer les migrations
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear

# Créer un utilisateur admin
php bin/console app:create-admin email@example.com password

# Lancer le serveur
symfony server:start
```

---

## 🐛 Dépannage

### Problème de mémoire PHP
```bash
php -d memory_limit=512M bin/console cache:clear
```

### Erreur CSRF Token
Vérifier que le token CSRF est bien désactivé dans `security.yaml` ou généré dans les formulaires.

### Email non envoyé
Vérifier la configuration `MAILER_DSN` dans `.env.local`

### API Discogs ne répond pas
Vérifier le token Discogs dans `.env.local`

---

## 📚 Documentation complémentaire

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation EasyAdmin](https://symfony.com/bundles/EasyAdminBundle/current/index.html)
- [API Discogs](https://www.discogs.com/developers)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)

---

## 👥 Auteurs

- **Développeur principal** : Votre Nom
- **Projet pédagogique** : Formation Développement Web

---

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

---

## 🙏 Remerciements

- API Discogs pour le catalogue de vinyles
- Communauté Symfony pour les bundles
- Font Awesome pour les icônes
- Google Fonts pour les polices

---

## 📞 Contact

Pour toute question ou suggestion :
- Email : contact@pickmyvinyl.com
- GitHub : https://github.com/votre-username/pick-my-vinyl

---

**Développé avec ❤️ et 🎵 par l'équipe Pick My Vinyl**

#   P i c k m y v i n y l  
 