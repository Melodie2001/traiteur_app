# Site Traiteur API (Symfony)

Bienvenue sur l'API de la plateforme de traiteur. Ce projet est développé avec **Symfony 7** et permet de mettre en relation des traiteurs et des clients.

## 📋 Table des Matières
- [Fonctionnalités](#-fonctionnalités)
- [Pré-requis](#-pré-requis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [Architecture](#-architecture)

## 🚀 Fonctionnalités
- **Utilisateurs** : Inscription et authentification (JWT) pour Traiteurs et Clients.
- **Traiteurs** : Création et gestion de cartes/menus.
- **Clients** : Demande de devis pour un menu, dépôt d'avis.

## 🛠 Pré-requis
Assurez-vous d'avoir installé :
- **PHP 8.2+**
- **Composer**
- **MySQL**
- **OpenSSL** (pour générer les clés JWT)

## 📦 Installation

1. **Cloner le projet** (si ce n'est pas déjà fait) :
   ```bash
   git clone <votre-url-repo>
   cd traiteur_app
   ```

2. **Installer les dépendances PHP** :
   ```bash
   composer install
   ```

3. **Générer les clés JWT** :
   Pour que l'authentification fonctionne, vous devez générer une paire de clés publique/privée.
   
   *En ligne de commande (Windows/Linux) :*
   ```bash
   # Créer le dossier s'il n'existe pas
   mkdir -p config/jwt
   
   # Générer la clé privée (avec passphrase)
   openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
   
   # Générer la clé publique
   openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
   ```
   > **Note :** Lors de la génération de la clé privée, entrez une "passphrase". Vous devrez reporter cette passphrase dans le fichier `.env` (variable `JWT_PASSPHRASE`).

## ⚙️ Configuration

### Base de Données & Environnement
Le fichier `.env` à la racine contient la configuration de l'application.

1. **Base de données MySQL** :
   Ouvrez `.env` et modifiez `DATABASE_URL` selon votre configuration locale (utilisateur/mot de passe).
   
   *Exemple par défaut (root / sans mot de passe) :*
   ```dotenv
   DATABASE_URL="mysql://root:@127.0.0.1:3306/traiteur_app?serverVersion=8.0&charset=utf8mb4"
   ```
   *Si vous avez un mot de passe (ex: 'root') :*
   ```dotenv
   DATABASE_URL="mysql://root:password@127.0.0.1:3306/traiteur_app?serverVersion=8.0&charset=utf8mb4"
   ```

2. **Créer la base et les tables** :
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

## ▶️ Utilisation

### Lancer le serveur local
```bash
php -S 127.0.0.1:8000 -t public
```
L'API sera accessible sur `http://127.0.0.1:8000`.

### Endpoints Principaux
Toutes les routes protégées nécessitent un header `Authorization: Bearer <votre_token>`.

| Action | Méthode | Route | Accès |
| :--- | :---: | :--- | :--- |
| **Inscription** | `POST` | `/api/register` | Public |
| **Connexion** | `POST` | `/api/login_check` | Public |
| **Créer Menu** | `POST` | `/api/traiteur/menu` | Traiteur |
| **Voir Menus** | `GET` | `/api/traiteur/menus` | Traiteur |
| **Liste Menus** | `GET` | `/api/public/menus` | Public |
| **Demander Devis**| `POST` | `/api/client/devis/{id}` | Client |

## 🏗 Architecture
Le projet respecte une architecture MVC simplifiée sans API Platform pour plus de légèreté.

### Entités
```mermaid
classDiagram
    class Users {
        +email
        +password
        +roles
    }
    class Menu {
        +titre
        +description
        +prix
    }
    class Devis {
        +dateEvenement
        +statut
    }
    class Avis {
        +compte
        +commentaire
    }

    Users "1" -- "*" Menu : Crée (Traiteur)
    Users "1" -- "*" Devis : Demande (Client)
    Users "1" -- "*" Avis : Ecrit (Client)
    Menu "1" -- "*" Devis : Concerne
    Menu "1" -- "*" Avis : Concerne
```
