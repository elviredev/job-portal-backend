# Grandes étapes de la mise en place du projet API job-portal avec Laravel 13

### Authentification JWT avec cookies HTTP only (inaccessible à Javascript)
- Le backend Laravel stocke le token dans un cookie HttpOnly
- Le frontend React n'a pas besoin de manipuler le token. Le navigateur envoie automatiquement le cookie.

## Étape 1 : Initialisation du projet Laravel
## Étape 2 : Configuration de la base de données MySQL
## Étape 3 : Modifier et ajouter des migrations (tables)
- Modifier "users" pour ajouter des colonnes supplémentaires 
- Créer une nouvelle migration pour "user_images"
- Créer une nouvelle migration pour "job_listings"
- Créer une nouvelle migration pour "descriptions"
- Créer une nouvelle migration pour "company_logos"
- Créer une nouvelle migration pour "applied_jobs"

## Étape 4 : Création des modèles Eloquent
- Créer les modèles "UserImage", "JobListing", "Description", "CompanyLogo", et "AppliedJob" avec les relations appropriées, les fillables et les casts.
- Mettre à jour le modèle "User" pour inclure les nouvelles colonnes et les relations avec les autres modèles.

## Étape 5 : Installation API - JWT et HTTP only cookies
- Installer le package JWT pour Laravel 13 en utilisant Composer :
```bash
composer require php-open-source-saver/jwt-auth
```

- Générer la clé secrète pour JWT :
```bash
php artisan jwt:secret
```

- Publier **LaravelServicesProvider** pour JWT :
```bash
php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
```

- Implémenter l'authentification JWT dans le model **User**
- Modifier la configuration dans le fichier **"auth.php"** pour utiliser le guard "jwt" :
```php
'guards' => [
  'api' => [
    'driver' => 'jwt',
    'provider' => 'users',
  ],
],
```

- Créer un middleware pour gérer les cookies HTTP only et sécuriser les routes de l'API.

```bash
php artisan make:middleware AttachJwtFromCookie
```

- Configurer le **middleware** pour extraire le token JWT des cookies HTTP only et l'attacher à la requête entrante.
- Mettre à jour le fichier **app.php** pour enregistrer le middleware afin qu'il s'exécute avant l'authentification JWT
- Ajouter dans ".env" la variable **"AUTH_GUARD=api"**
- Créer un fichier **"cors.php"** dans le dossier **"config"** pour gérer les CORS et permettre les requêtes depuis le front-end.

## Étape 6 🗄️ : Création du contrôleur JobController et de StoreJobRequest

```bash
php artisan make:controller JobController
php artisan make:request StoreJobRequest
```

- créer la méthode `store()` pour ajouter une nouvelle offre d'emploi 
- créer un utilisateur dans la table 'users' avec un rôle de recruiter pour tester

```bash
insert into users (first_name, last_name, email, password, role, is_active, created_at, updated_at) 
values ('Test', 'User', 'test@test.com', 'password123', 'recruiter', true, NOW(), NOW());
```

- Créer les dossiers de stockage dans "/storage/app/public" pour les logos d'entreprise, les images d'utilisateur et les cv : "company_logos", "user_images", "resumes"

## Étape 7 🗄️ : Création des routes API
- Ajouter la route POST pour créer une nouvelle offre d'emploi dans le fichier **api.php**
- Ajouter le group "middleware" pour sécuriser la route GET des offres d'emploi avec le middleware AttachJwtFromCookie et l'authentification JWT.

## Étape 8 📱 : Test des routes API avec le frontend React
- Mettre à jour **"Create.jsx"** pour envoyer les données du formulaire à l'API via la route POST "/api/jobs"

## Étape 9 📱 : Implémenter les notifications côté frontend
- Installer react-toastify pour afficher les notifications de succès ou d'erreur lors de la création d'une offre d'emploi.

```bash
npm install react-toastify
```

- Ajouter le ToastContainer dans **"main.jsx"** pour permettre l'affichage des notifications dans toute l'application.
- Dans le composant qui déclenche l'action, importer **"toast"** et utiliser les méthodes `toast.success()` et `toast.error()` pour afficher les notifications appropriées en fonction de la réponse de l'API.

## Étape 10 📱 : RecruiterLogin.jsx
- Gérer la connexion du recruteur via Google ou en mode manuel avec email et mot de passe.

## Étape 11 🗄️ : Créer un controller "AuthController" et "GoogleLoginController" et un middleware "RoleMiddleware" pour vérifier le rôle de l'utilisateur
- Créer un controller "**Auth/AuthController**" pour gérer l'authentification et la vérification du rôle de l'utilisateur.
- Créer un controller "**Auth/GoogleLoginController**" pour gérer la connexion via Google OAuth.
- Créer un middleware "**RoleMiddleware**" pour vérifier si l'utilisateur connecté a le rôle de "recruiter" avant de lui permettre d'accéder à certaines routes de l'API.
- Ajouter la communication Laravel avec Google OAuth pour permettre aux utilisateurs de se connecter via leur compte Google.

```bash
composer require google/apiclient
```
- Compléter **"RoleMiddleware"** pour vérifier le rôle de l'utilisateur et rediriger ou renvoyer une réponse appropriée si l'utilisateur n'a pas le rôle requis.
- Enregistrer le middleware dans **"app.php"** pour qu'il soit disponible dans les routes de l'API.
- Dans le model "User", implémenter la fonction `getJWTCustomClaims()` pour retourner le "role" de l'utilisateur dans le payload du token JWT.
- Dans le model "User", créer une fonction `getFullNameAttribute()` pour retourner le nom complet de l'utilisateur en combinant les colonnes "first_name" et "last_name" car c'est nécessaire pour Google Login

### Google Login
- Implémenter **"GoogleLoginController"**, méthode `googleLogin()`
- Ajouter les variables "GOOGLE_CLIENT_ID", "GOOGLE_CLIENT_SECRET" et "GOOGLE_REDIRECT_URI" dans le fichier **".env"** pour la configuration de Google OAuth.
- Dans Google console (https://console.cloud.google.com/auth/clients/264818795334-3mvc62t80t334if9mu707ss872p2bmc8.apps.googleusercontent.com?authuser=2&project=my-react-app-499214), modifier les URI :
  - Origines JavaScript autorisées : http://localhost:5173 et http://127.0.0.1:5173
  - URI de redirection autorisés : http://localhost:8000/api/auth/google/callback
- tester l'inscription "Recruiter Login" via Google (hors pulse secure) et vérifier si le user 'elviredev@gmail.com' est bien enregistré en bdd

### Login avec email et mot de passe
- Implémenter **"AuthController"**, méthodes `register()`, `login()`, `me()` (pour savoir qui est le user connecté) et `logout()`

## Étape 12 📱 : Gérer l'authentification et Logout côté Front React
- Créer **AuthContext.jsx**, **ProtectedRoute.jsx**
- Mettre à jour **App.jsx** pour gérer les routes selon le role
- Ajouter la gestion de "Logout" dans la **"Navbar"** et ajouter les données de l'utilisateur (nom, image...) selon le rôle.
- Mettre à jour les routes dans "api.php" pour l'authentification : **"/auth/logout"**, **"/auth/me"** (permet de récupérer les données fake)

### Flux de connexion d'un utilisateur avec Google
- Login Google réussi (Google authentifie le user)
- Google renvoie un **ID Token** au frontend React (`credentialResponse.credential`).
- React envoie ce token à Laravel (`handleSuccess()`)
- Laravel vérifie le token auprès de Google (`googleLogin()`)
- Laravel récupère les informations Google (email, prénom, avatar, etc.).
- Laravel crée ou retrouve l'utilisateur dans la base de données.
- Laravel génère un token d'authentification (**auth_token**).
- Laravel place ce token dans un **cookie HttpOnly** (_Set-Cookie: auth_token=xxxxx; HttpOnly; Secure; SameSite=None_)
- Le navigateur stocke automatiquement le cookie.
- React appelle `refreshUser()` 
- `refreshUser()` exécute : `GET /auth/me` qui permet de **récupérer le user à partir du JWT**
- Le navigateur envoie automatiquement le cookie (_Cookie: auth_token=xxxxx_)
- Laravel lit le cookie "**auth_token**" (`me()`)
- Laravel identifie l'utilisateur connecté
- Laravel renvoie les données utilisateur au frontend
- React `setUser()` met à jour le contexte
- Tous les composants utilisant `const { user } = useAuth()` sont re-rendus automatiquement.
- Redirection vers '/' ou '/jobs'
- La Navbar affiche immédiatement : hi, Sandrine et la photo Google

### Vérification d'authentification au chargement de l'application
- L'application React démarre
- "**AuthContext**" est monté
- Le **useEffect()** exécute : `refreshUser()`
- React appelle : `GET /auth/me`
- Le navigateur envoie automatiquement : _Cookie: auth_token=xxxxx_
- Laravel vérifie le cookie.
- Laravel renvoie l'utilisateur connecté.
- `setUser()` met à jour le contexte.
- Toute l'application connaît immédiatement l'utilisateur connecté.

### Déconnexion
- L'utilisateur clique sur Logout.
- React appelle : `POST /auth/logout`
- Le navigateur envoie automatiquement : _Cookie: auth_token=xxxxx_
- Laravel invalide le token
- Laravel supprime le cookie : _Set-Cookie: auth_token=deleted;_
- Le navigateur supprime le cookie
- React exécute : `setUser(null)`
- Tous les composants utilisant `useAuth()` sont re-rendus
- La Navbar n'affiche plus : Hi, Sandrine
- La Navbar affiche : Recruiter login / Login

- Le point clé est que **le cookie "auth_token" circule automatiquement entre le navigateur et Laravel**, tandis que **le state "user" du contexte sert uniquement à mettre à jour l'interface React en temps réel**.

## Étape 13 🗄️ : HomePage - Récupérer les jobs, implémenter les filtres de recherche côté backend
- Définir la méthode `index()` dans **"JobController"**
- Créer une Resource **"JobListingResource"**
- Décommenter `'user_id' => auth('api')->id()` dans "JobController"

## Étape 14 📱 : Implémener les filtres de recherche côté frontend et la pagination
- Mettre à jour les composants **"ListingJobs.jsx"**, **"FilteredJobs.jsx"**, **"Hero.jsx"**, **"Home.jsx"**
- Créer un hook personnalisé `useDebounce` pour optimiser la recherche par keyword et location 

## Étape 15 🗄️ : Manage Jobs pour le recruteur côté backend (Partie Dashboard)
- Créer la route "myJobs" dans api.php
- Créer la méthode `myJobs()` dans "**JobController**"

## Étape 16 📱 : Implémenter ManagedJobs.jsx
- Implémenter le contenu de la page

## Étape 17 🗄️ : Deleting job côté Laravel
- Créer la route "destroy" dans api.php
- Implementer la méthode `destroy()`

## Étape 18 📱 : Deleting job côté React
- Créer un component **"ConfirmModal.jsx"** et l'importer dans "ManagedJobs.jsx"

## Étape 19 🗄️ : Show job by ID côté Laravel
- Créer une "**UserResource**" pour pouvoir contrôler les infos du recruteur exposées à l'API
- Ajouter la relation **"UserResource"** dans **"JobListingResource"** pour intégrer le "recruiter" dedans avec `whenLoaded()` qui évite des requêtes SQL supplémentaires si la relation n'a pas été chargée donc évite le lazy loading (N+1)
- Créer la route `"show()"` dans api.php
- Créer la méthode `"show()"` dans JobController

## Étape 20 📱 : Show job by ID côté React
- Mettre à jour les routes dans **"App.jsx"**
- Mettre à jour **"Edit.jsx"**

## Étape 21 🗄️ : Update Job
- Créer la route "**update**" dans api.php
- Créer "**UpdateStoreRequest**" pour gérer la validation des champs
- Créer la méthode `"update()"` dans JobController

## Étape 22 🗄️ : Update profile côté backend
- Créer la route "**updateProfile**" dans api.php
- Créer la méthode `"updateProfile()"` dans "**AuthController**"
- Créer une méthode privée `deleteExistingImage()` pour éviter de dupliquer le code pour gérer la suppression de l'image existante
- Mettre à jour "**UserResource**" pour ajouter des infos 

## Étape 23 📱 : Update profile côté frontend
- Implémenter **"EditedProfile.jsx"**

## Étape 24 🗄️ : Job Details côté backend
- Créer la route publique `"showPublic"` dans api.php : accès public aux details du job
- Créer la méthode `"showPublic()"` dans "**JobController**"

## Étape 25 📱 : Job Details côté frontend
- Implémenter **"JobDetails.jsx"**
- Ajouter la méthode `getDaysAgo()` dans formatter.js
- Vérifier le statut "**applied**" pour afficher si un job a été postulé par un candidat. Implémenter la méthode `checkIfApplied()`
- Gestion des boutons "Login to Apply", "Apply for this Job", "Already Apply" selon le role visiteur, candidat, recruteur.

## Étape 26 🗄️ : Check Applied Status
- Créer la route **"applied-jobs/check"** dans api.php. Cette route est accessible uniquement aux utilisateurs connectés, pas aux recruteurs pour permettre de pvérifier si un job a été postulé par le user connecté ou pas.
- Créer le controller "**SavedJobController**"
- Créer la méthode `"checkApplied()"` dans "**SavedJobController**"

## Étape 27 🗄️ : Recruiter Signup (hors compte Google)
- Modifier la méthode `register()` dans "**AuthController**" : 
  - supprimer "admin" de la validation du role pour des raisons de sécurité
  - supprimer la génération du token dans register() et la laisser dans login()

## Étape 28 📱 : Recruiter Signup (hors compte Google)
- Implémenter la logique "**RecruiterSignup.jsx**"
- Ajouter l'appel à `refreshUser()` dans "**RecruiterLogin.jsx**" afin de mettre à jour le context React immédiatement après la connexion.

## Étape 29 📱 : UserSignup et UserLogin
- Faire un copier/coller de "RecruiterSignup" et "RecruiterLogin" et adapter
- Mettre à jour les redirections suite à un **logout** dans **"Navbar.jsx"**


## Étape 30 📱 : Update UserProfile côté frontend
- Implémenter **"UserProfile.jsx"**
- Reprendre ce qui a été fait dans "EditedProfile" pour le recruiter et adapter notamment avec le fullName

## Étape 31 🗄️ : Apply Job Process côté backend
- Créer la route POST **"applied-jobs"** dans api.php.
- Créer la méthode `"apply()"` dans "**SavedJobController**"
- Créer "**ApplyJobRequest**" pour la validation des données du formulaire.
- Créer une resource "**AppliedJobResource**" pour contrôler ce que l'api retourne

## Étape 32 📱 : Apply Job Process côté frontend
- Implémenter "**ApplyJobModal.jsx**"

## Étape 33 🗄️ : Get & Delete Applied Job côté backend
- Créer la route GET **"applied-jobs"** et DELETE **"applied-jobs/{id}"** dans api.php.
- Créer les méthodes `getAppliedJobs()` et `destroy()` dans "**SavedJobController**"

## Étape 34 📱 : Applied Job Listings côté frontend
- Implémenter **"AppliedJobListings.jsx"**

## Étape 35 🗄️ : View Applications côté backend
- Créer les routes GET "**applications**" et PATCH "**applications/{application}/status**"
- Créer les méthodes `getApplications()` et `updateStatus()` dans "**JobController**"

## Étape 36 📱 : View Applications côté frontend
- Implémenter "**ViewApplications.jsx**"
- Créer le component "**DropdownAction.jsx**"














