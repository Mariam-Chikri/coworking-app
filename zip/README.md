# 🏢 CoWorkSpace — Guide d'installation complet

> **Plateforme de gestion d'espace de coworking** — Laravel 10 + Livewire 3 + API Sanctum

## 📋 Fonctionnalités

| Fonctionnalité | Description |
|---|---|
| 🗓️ **Réservations** | Système complet de réservation avec numérotation automatique |
| ⏰ **Prolongation** | Prolonger une réservation en cours (1-8h, vérification dispo) |
| 🕊️ **Libération anticipée** | Libérer l'espace avant l'heure — prix recalculé automatiquement |
| 📄 **Factures PDF** | Génération automatique + téléchargement PDF via DomPDF |
| ❤️ **Favoris** | Sauvegarder ses espaces préférés |
| ⭐ **Avis** | Système d'avis modérés (1-5 étoiles) |
| 📊 **Stats avancées** | Taux d'occupation, revenus mensuels, espaces populaires |
| 🎛️ **Dashboard Admin** | Graphiques Chart.js, gestion avis, réservations récentes |
| 🤖 **Chatbot FAQ** | Assistant interactif FR/EN |
| 🌍 **Bilingue FR/EN** | Changement de langue à la volée |
| 🎨 **CSS Custom** | Poppins + dégradés #667eea/#764ba2 |

## ✅ Prérequis

- PHP 8.1+
- Composer 2+
- MySQL 8.0+ ou MariaDB 10.6+
- Node.js 18+ et npm (pour les assets)
- Extension PHP : `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`

---

## 🚀 Installation étape par étape

### 1. Copier les fichiers dans un projet Laravel vierge

```bash
# Créer un nouveau projet Laravel 10
composer create-project laravel/laravel coworking-app "^10.0"
cd coworking-app
```

Copiez **tous les fichiers du ZIP** dans le dossier du projet :

```bash
# Remplacez /chemin/vers/coworking-livewire par le dossier du ZIP extrait
cp -r /chemin/vers/coworking-livewire/* .
```

### 2. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Editez `.env` et renseignez votre base de données :
```env
DB_DATABASE=coworking
DB_USERNAME=votre_user
DB_PASSWORD=votre_mot_de_passe
APP_URL=http://localhost:8000
```

### 3. Installer les dépendances PHP

```bash
composer install
```

Vérifiez que ces packages sont installés :
- `livewire/livewire:^3.0`
- `laravel/sanctum:^3.2`
- `barryvdh/laravel-dompdf:^2.0`

### 4. Configurer `bootstrap/app.php` (Laravel 10) ou `Kernel.php`

#### Pour Laravel 10 avec Kernel.php :
Dans `app/Http/Kernel.php`, ajoutez dans `$middlewareAliases` :

```php
'admin' => \App\Http\Middleware\AdminMiddleware::class,
'locale' => \App\Http\Middleware\LocaleMiddleware::class,
```

Et dans `$middlewareGroups['web']`, ajoutez :
```php
\App\Http\Middleware\LocaleMiddleware::class,
```

#### Pour Laravel 11+ (bootstrap/app.php) :
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\LocaleMiddleware::class,
    ]);
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'locale' => \App\Http\Middleware\LocaleMiddleware::class,
    ]);
})
```

### 5. Publier les configurations Livewire et Sanctum

```bash
php artisan vendor:publish --provider="Livewire\LivewireServiceProvider"
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 6. Enregistrer les composants Livewire

Dans `app/Providers/AppServiceProvider.php` ou via les noms automatiques (Livewire v3 auto-découvre les composants dans `app/Http/Livewire`).

Si auto-découverte ne fonctionne pas, enregistrez manuellement dans `AppServiceProvider::boot()` :

```php
use Livewire\Livewire;
use App\Http\Livewire\{EspacesList, MesReservations, ReservationForm, AdminDashboard, ChatbotFaq, AvisComponent};

Livewire::component('espaces-list', EspacesList::class);
Livewire::component('mes-reservations', MesReservations::class);
Livewire::component('reservation-form', ReservationForm::class);
Livewire::component('admin-dashboard', AdminDashboard::class);
Livewire::component('chatbot-faq', ChatbotFaq::class);
Livewire::component('avis-component', AvisComponent::class);
```

### 7. Configurer DomPDF (factures PDF)

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### 8. Créer la base de données et lancer les migrations

```bash
# Créer la base (ou via phpMyAdmin)
mysql -u root -p -e "CREATE DATABASE coworking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Lancer les migrations
php artisan migrate

# Peupler avec les données de test
php artisan db:seed
```

### 9. Copier le CSS dans `public/css/`

```bash
mkdir -p public/css
cp resources/css/coworking.css public/css/coworking.css
```

> **Ou utilisez Vite** en modifiant `vite.config.js` pour inclure `resources/css/coworking.css`.

### 10. Créer le symlink storage

```bash
php artisan storage:link
```

### 11. Lancer le serveur

```bash
php artisan serve
```

Accédez à : **http://localhost:8000**

---

## 🔑 Comptes par défaut (après seeding)

| Rôle | Email | Mot de passe |
|---|---|---|
| **Admin** | admin@coworking.fr | password |
| Utilisateur | marie@example.fr | password |
| Utilisateur EN | john@example.com | password |

---

## 🛠️ Routes importantes

### Web
| Route | Description |
|---|---|
| `/` | Page d'accueil |
| `/espaces` | Liste des espaces avec filtres Livewire |
| `/espaces/{id}` | Détail + formulaire de réservation |
| `/reservations` | Mes réservations (prolongation, libération, annulation) |
| `/factures` | Mes factures PDF |
| `/favoris` | Mes espaces favoris |
| `/admin/dashboard` | Dashboard admin avec graphiques |
| `/lang/fr` ou `/lang/en` | Changer la langue |

### API (Sanctum)
| Méthode | Route | Description |
|---|---|---|
| POST | `/api/auth/register` | Inscription |
| POST | `/api/auth/login` | Connexion → token |
| GET | `/api/espaces` | Liste espaces |
| POST | `/api/reservations` | Créer réservation |
| POST | `/api/reservations/{id}/prolonger` | Prolonger |
| POST | `/api/reservations/{id}/liberer` | Libération anticipée |
| DELETE | `/api/reservations/{id}` | Annuler |
| GET | `/api/factures/{id}/pdf` | Télécharger PDF |
| POST | `/api/espaces/{id}/favoris` | Toggle favori |
| POST | `/api/avis` | Soumettre avis |
| GET | `/api/admin/stats` | Stats admin |

#### Utiliser l'API avec Sanctum :
```bash
# 1. Se connecter
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@coworking.fr","password":"password"}'

# 2. Utiliser le token retourné
curl http://localhost:8000/api/espaces \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

---

## 🏗️ Structure du projet

```
app/
├── Http/
│   ├── Controllers/Api/    # AuthController, EspaceController, ReservationController,
│   │                         FactureController, AvisController, FavoriController, StatsController
│   ├── Livewire/           # EspacesList, MesReservations, ReservationForm,
│   │                         AdminDashboard, ChatbotFaq, AvisComponent
│   └── Middleware/         # AdminMiddleware, LocaleMiddleware
├── Models/                 # User, Espace, Reservation, Facture, Favori, Avis
└── Policies/               # ReservationPolicy

database/
├── migrations/             # 6 migrations (espaces, reservations, factures, favoris, avis, users)
└── seeders/                # DatabaseSeeder, UserSeeder, EspaceSeeder (9 espaces)

resources/
├── views/
│   ├── layouts/app.blade.php          # Layout principal (navbar bilingue, footer, toasts, chatbot)
│   ├── home.blade.php                  # Page d'accueil (hero, étapes, espaces à la une, à propos)
│   ├── livewire/                       # Composants Livewire (list, form, dashboard, chatbot...)
│   ├── espaces/                        # Index + show avec formulaire réservation
│   ├── auth/                           # Login + register stylisés
│   ├── factures/                       # Liste + template PDF
│   ├── reservations/                   # Mes réservations
│   ├── favoris/                        # Mes favoris
│   └── admin/                          # Dashboard admin
├── css/coworking.css                   # CSS complet custom
└── lang/fr|en/messages.php             # Traductions FR/EN
```

---

## 🎨 Personnalisation CSS

Les variables CSS sont dans `resources/css/coworking.css` :

```css
:root {
    --primary: #667eea;
    --primary-dark: #764ba2;
    --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    /* Modifiez ces valeurs pour changer toute la palette */
}
```

---

## 🐛 Problèmes courants

**Livewire "Component not found"**
→ Vider le cache : `php artisan view:clear && php artisan cache:clear`
→ Vérifier les namespaces dans `app/Http/Livewire/`

**"SQLSTATE: Table doesn't exist"**
→ Vérifier l'ordre des migrations (les noms de fichiers commencent par la date)
→ Relancer : `php artisan migrate:fresh --seed`

**PDF ne génère pas**
→ Vérifier l'extension PHP `gd` ou `imagick`
→ `composer require barryvdh/laravel-dompdf`

**Admin 403**
→ Vérifier que `is_admin = 1` dans la table `users` pour le compte admin
→ Relancer le seeder : `php artisan db:seed --class=UserSeeder`

**Langue ne change pas**
→ Vérifier que `LocaleMiddleware` est bien dans le groupe `web`
→ Créer les dossiers : `resources/lang/fr/` et `resources/lang/en/`

---

## 📦 Dépendances clés

```json
{
  "laravel/framework": "^10.0",
  "livewire/livewire": "^3.0",
  "laravel/sanctum": "^3.2",
  "barryvdh/laravel-dompdf": "^2.0"
}
```

CDN chargés dans le layout (pas besoin d'npm) :
- **Font Awesome 6** (icônes)
- **Google Fonts Poppins** (typographie)
- **Chart.js 4** (graphiques admin)

---

## 🤝 Contribution

Structure conçue pour être facilement étendue :
- Ajouter un type d'espace → `EspaceSeeder` + traduction `messages.php`
- Ajouter une FAQ → `ChatbotFaq::$faq`
- Ajouter un graphique admin → `AdminDashboard.php` + `admin-dashboard.blade.php`

---

*CoWorkSpace — Développé avec ❤️ sous Laravel 10 + Livewire 3*
