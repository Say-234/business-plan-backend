<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## 📚 Documentation API

Cette application dispose d'une documentation API complète générée avec **Scribe**. L'API est entièrement documentée en français et couvre tous les endpoints disponibles.

### 🚀 Générer la documentation

Pour générer la documentation API, exécutez la commande suivante :

```bash
php artisan scribe:generate
```

### 🌐 Accéder à la documentation

Après génération, la documentation sera disponible aux URLs suivantes :

- **Documentation web interactive** : `http://votre-domaine.com/docs`
- **Fichier HTML** : `public/docs/index.html`
- **Collection Postman** : `public/docs/collection.json`

### 📋 Endpoints disponibles

L'API couvre les modules suivants :

#### 🔐 **Authentification**
- Inscription et connexion
- Vérification email
- Réinitialisation mot de passe
- Authentification Google

#### 👤 **Gestion Utilisateur**
- Profil utilisateur
- Notifications
- Projets

#### 💰 **Finances** (API complète)
- Gestion des produits financiers
- Capital de démarrage (pré-exploitation, immobilisations, fonds de roulement)
- Ventes prévisionnelles
- Emprunts et financements
- Calcul automatique du flux de trésorerie

#### 📊 **Évaluation**
- Évaluation de documents
- Génération de réponses IA

#### 🤖 **Intelligence Artificielle**
- Génération de contenu
- Assistance business plan

#### 📄 **Documents**
- Business plans
- CV et lettres de motivation
- Partenaires

#### 💳 **Paiements**
- Intégration FedaPay
- Gestion des transactions

### 🔧 Configuration Scribe

La configuration Scribe se trouve dans `config/scribe.php`. Les principales fonctionnalités :

- **Documentation en français** avec exemples détaillés
- **Groupes logiques** pour organiser les endpoints
- **Exemples de requêtes et réponses** pour chaque endpoint
- **Authentification** documentée pour les endpoints protégés
- **Codes de statut HTTP** avec descriptions

### 📝 Exemple d'utilisation

```bash
# Générer la documentation
php artisan scribe:generate

# Servir l'application
php artisan serve

# Accéder à la documentation
# Ouvrir http://localhost:8000/docs dans votre navigateur
```

### 🔄 Mise à jour de la documentation

La documentation se met à jour automatiquement à chaque modification des docblocks dans les contrôleurs. Pour régénérer manuellement :

```bash
php artisan scribe:generate --force
```

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
