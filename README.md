# PHP MySQL API — Apprends en construisant

Ce projet est un exercice pour apprendre à créer une API en PHP avec MySQL.

## Structure du projet

```
PHP_MYSQL_API/
├── data/
│   ├── users.json              ← données de départ
│   └── produits.json           ← données de départ
├── api/
│   ├── config/
│   │   └── database.php        ← connexion MySQL (PDO)
│   ├── middleware/
│   │   └── auth.php            ← fonctions JWT (fait main)
│   ├── seed.php                ← remplit la DB depuis les JSON
│   ├── login.php               ← POST → retourne un JWT
│   ├── users.php               ← GET → liste des users (protégé)
│   └── produits.php            ← GET → liste des produits (protégé)
├── front/
│   └── index.html              ← frontend (formulaire + boutons)
└── README.md
```

> **Pas de framework, pas de Composer, pas de Docker.** Juste PHP + MySQL + un navigateur.

---

## Pré-requis

```bash
# Vérifier que PHP est installé
php -v

# Vérifier que MySQL est installé et tourne
mysql --version
```

---

# ÉTAPE 1 — Lancer le serveur PHP intégré

**But :** Démarrer un serveur web sans Apache ni Nginx.

### Exercice 1.1 — Lancer le serveur

Depuis la racine `PHP_MYSQL_API/`, lance le serveur intégré de PHP :

```bash
php -S localhost:8080
```

Tout le dossier devient accessible sur `http://localhost:8080/`.

### Exercice 1.2 — Tester

Crée le fichier `front/index.html` avec juste :

```html
<h1>Hello PHP Learn</h1>
```

Ouvre `http://localhost:8080/front/index.html` → tu dois voir le titre.

---

# ÉTAPE 2 — Créer la base MySQL

**But :** Créer la base de données depuis le terminal MySQL.

### Exercice 2.1 — Se connecter à MySQL et créer la base

```bash
mysql -u root -p
```

Puis tape cette commande SQL :

```sql
CREATE DATABASE IF NOT EXISTS php_learn;
```

<details>
<summary>💡 Indice — Vérifier que la base existe</summary>

```sql
SHOW DATABASES;
```

Tu dois voir `php_learn` dans la liste.

</details>

---

# ÉTAPE 3 — Connexion PHP → MySQL avec PDO

**But :** Écrire une classe PHP qui se connecte à MySQL.

### Exercice 3.1 — Crée `api/config/database.php`

Ce fichier doit contenir une **classe** `Database` avec :
- Des propriétés privées : `$host`, `$db_name`, `$username`, `$password`
- Une méthode publique `getConnection()` qui retourne un objet `PDO`

Les valeurs :

| Propriété | Valeur |
|-----------|--------|
| host | `localhost` |
| db_name | `php_learn` |
| username | `root` |
| password | *(ton mot de passe MySQL)* |

<details>
<summary>💡 Indice 1 — Déclarer une classe PHP</summary>

```php
<?php
class NomDeLaClasse {
    private $propriete = "valeur";

    public function maMethode() {
        return $this->propriete;
    }
}
```

</details>

<details>
<summary>💡 Indice 2 — Créer une connexion PDO</summary>

```php
$dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
$this->conn = new PDO($dsn, $this->username, $this->password);
```

</details>

<details>
<summary>💡 Indice 3 — Activer les erreurs PDO</summary>

```php
$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
```

</details>

<details>
<summary>✅ Solution complète</summary>

```php
<?php
class Database {
    private $host = "localhost";
    private $db_name = "php_learn";
    private $username = "root";
    private $password = "root";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur connexion : " . $e->getMessage();
        }
        return $this->conn;
    }
}
```

</details>

---

# ÉTAPE 4 — Seed : Remplir la BDD depuis les fichiers JSON

**But :** Lire les JSON, créer les tables, insérer les données.

### Exercice 4.1 — Crée `api/seed.php`

Ce script doit :
1. Inclure `config/database.php` et se connecter
2. Créer la table `users` puis la table `produits`
3. Lire les fichiers `data/users.json` et `data/produits.json`
4. Insérer chaque entrée avec une **requête préparée**
5. **Hasher les mots de passe** avant insertion (jamais en clair !)

**Table `users` :**

| Colonne | Type | Contrainte |
|---------|------|------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY |
| nom | VARCHAR(100) | NOT NULL |
| prenom | VARCHAR(100) | NOT NULL |
| email | VARCHAR(150) | UNIQUE NOT NULL |
| mot_de_passe | VARCHAR(255) | NOT NULL |

**Table `produits` :**

| Colonne | Type | Contrainte |
|---------|------|------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY |
| nom | VARCHAR(150) | NOT NULL |
| prix | DECIMAL(10,2) | NOT NULL |
| categorie | VARCHAR(100) | NOT NULL |
| stock | INT | NOT NULL |

<details>
<summary>💡 Indice 1 — Lire un fichier JSON en PHP</summary>

```php
$json = file_get_contents(__DIR__ . '/../data/users.json');
$users = json_decode($json, true);  // true → tableau associatif
```

</details>

<details>
<summary>💡 Indice 2 — Créer une table</summary>

```php
$conn->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
)");
```

</details>

<details>
<summary>💡 Indice 3 — Requête préparée (sécurisé contre injection SQL)</summary>

```php
$stmt = $conn->prepare("INSERT INTO users (nom, email) VALUES (:nom, :email)");
$stmt->execute([':nom' => $user['nom'], ':email' => $user['email']]);
```

</details>

<details>
<summary>💡 Indice 4 — Hasher un mot de passe</summary>

```php
$hash = password_hash($user['mot_de_passe'], PASSWORD_DEFAULT);
```

> Ne JAMAIS stocker un mot de passe en clair !

</details>

<details>
<summary>💡 Indice 5 — Boucle foreach</summary>

```php
foreach ($users as $user) {
    // $user['nom'], $user['email'], etc.
}
```

</details>

<details>
<summary>✅ Solution complète</summary>

```php
<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

// --- Créer les tables ---
$conn->exec("DROP TABLE IF EXISTS users");
$conn->exec("DROP TABLE IF EXISTS produits");

$conn->exec("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL
)");

$conn->exec("CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    categorie VARCHAR(100) NOT NULL,
    stock INT NOT NULL
)");

// --- Seed Users ---
$json = file_get_contents(__DIR__ . '/../data/users.json');
$users = json_decode($json, true);
$stmt = $conn->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe) VALUES (:nom, :prenom, :email, :mdp)");

foreach ($users as $u) {
    $hash = password_hash($u['mot_de_passe'], PASSWORD_DEFAULT);
    $stmt->execute([
        ':nom' => $u['nom'],
        ':prenom' => $u['prenom'],
        ':email' => $u['email'],
        ':mdp' => $hash
    ]);
}

// --- Seed Produits ---
$json = file_get_contents(__DIR__ . '/../data/produits.json');
$produits = json_decode($json, true);
$stmt = $conn->prepare("INSERT INTO produits (nom, prix, categorie, stock) VALUES (:nom, :prix, :cat, :stock)");

foreach ($produits as $p) {
    $stmt->execute([
        ':nom' => $p['nom'],
        ':prix' => $p['prix'],
        ':cat' => $p['categorie'],
        ':stock' => $p['stock']
    ]);
}

echo json_encode(["message" => "Seed terminé", "users" => count($users), "produits" => count($produits)]);
```

**Teste :** `http://localhost:8080/api/seed.php`

</details>

---

# ÉTAPE 5 — Les endpoints GET (sans auth pour l'instant)

**But :** Créer des fichiers PHP qui retournent du JSON.

### Exercice 5.1 — Crée `api/produits.php`

Ce fichier doit :
1. Mettre le header `Content-Type: application/json`
2. Vérifier que la méthode est `GET` (sinon → `405`)
3. Se connecter à la base
4. Faire `SELECT * FROM produits`
5. Retourner le résultat en JSON

<details>
<summary>💡 Indice 1 — Vérifier la méthode HTTP</summary>

```php
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée"]);
    exit;
}
```

</details>

<details>
<summary>💡 Indice 2 — SELECT + fetchAll</summary>

```php
$stmt = $conn->query("SELECT * FROM produits");
$produits = $stmt->fetchAll();
```

</details>

<details>
<summary>✅ Solution complète</summary>

```php
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée"]);
    exit;
}

require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->query("SELECT id, nom, prix, categorie, stock FROM produits");
$produits = $stmt->fetchAll();

echo json_encode($produits);
```

</details>

### Exercice 5.2 — Crée `api/users.php`

Pareil pour les users. **ATTENTION : ne JAMAIS retourner le mot de passe !**

<details>
<summary>💡 Indice — Sélectionner sans le mot de passe</summary>

```php
$stmt = $conn->query("SELECT id, nom, prenom, email FROM users");
// PAS de SELECT * → on exclut mot_de_passe
```

</details>

<details>
<summary>✅ Solution complète</summary>

```php
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée"]);
    exit;
}

require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->query("SELECT id, nom, prenom, email FROM users");
$users = $stmt->fetchAll();

echo json_encode($users);
```

</details>

### Exercice 5.3 — Teste avec curl

```bash
curl http://localhost:8080/api/produits.php
curl http://localhost:8080/api/users.php
```

---

# ÉTAPE 6 — JWT fait main (sans librairie)

**But :** Comprendre JWT en le codant toi-même, sans Composer ni librairie externe.

### Comprendre JWT (pas de code)

Un JWT = 3 morceaux séparés par des `.` :

```
HEADER.PAYLOAD.SIGNATURE
```

- **Header** → `{"alg":"HS256","typ":"JWT"}` encodé en base64url
- **Payload** → tes données (id, email, expiration) encodées en base64url
- **Signature** → HMAC-SHA256 de `header.payload` avec ta clé secrète

### Exercice 6.1 — Crée `api/middleware/auth.php`

Ce fichier doit contenir :

1. Une constante `JWT_SECRET`
2. `base64url_encode($data)` — comme base64 mais avec `-_` au lieu de `+/`
3. `base64url_decode($data)` — l'inverse
4. `generer_jwt($payload)` — crée un token (expire dans 1h)
5. `verifier_jwt($token)` — vérifie la signature, l'expiration, retourne le payload ou `null`
6. `get_user_from_token()` — lit le header `Authorization: Bearer xxx`, vérifie, retourne le user ou `null`

<details>
<summary>💡 Indice 1 — base64url (pas base64 standard !)</summary>

```php
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}
```

</details>

<details>
<summary>💡 Indice 2 — Créer la signature HMAC</summary>

```php
$signature = base64url_encode(
    hash_hmac('sha256', "$header.$payload", $secret, true)
);
```

Le `true` à la fin = sortie binaire (pas hex).

</details>

<details>
<summary>💡 Indice 3 — Séparer un token en 3 parties</summary>

```php
$parts = explode('.', $token);
if (count($parts) !== 3) return null;
list($header, $payload, $signature) = $parts;
```

</details>

<details>
<summary>💡 Indice 4 — Lire le header Authorization</summary>

```php
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
if (strpos($auth, 'Bearer ') !== 0) return null;
$token = substr($auth, 7);
```

</details>

<details>
<summary>✅ Solution complète</summary>

```php
<?php
define('JWT_SECRET', 'ma_cle_secrete_php_learn');

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function generer_jwt($payload) {
    $header = base64url_encode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    $payload['exp'] = time() + 3600;
    $payload_encoded = base64url_encode(json_encode($payload));

    $signature = base64url_encode(
        hash_hmac('sha256', "$header.$payload_encoded", JWT_SECRET, true)
    );

    return "$header.$payload_encoded.$signature";
}

function verifier_jwt($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    list($header, $payload, $signature) = $parts;

    // Recalculer la signature pour vérifier
    $valid_signature = base64url_encode(
        hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
    );

    if ($signature !== $valid_signature) return null;

    $data = json_decode(base64url_decode($payload), true);

    // Vérifier expiration
    if (isset($data['exp']) && $data['exp'] < time()) return null;

    return $data;
}

function get_user_from_token() {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? '';

    if (strpos($auth, 'Bearer ') !== 0) return null;

    $token = substr($auth, 7);
    return verifier_jwt($token);
}
```

</details>

---

# ÉTAPE 7 — Endpoint Login

**But :** Recevoir email + mot de passe en POST, vérifier en base, retourner un JWT.

### Exercice 7.1 — Crée `api/login.php`

Ce fichier doit :
1. Accepter uniquement **POST**
2. Lire le body JSON (`email` + `mot_de_passe`)
3. Chercher l'utilisateur en base par email
4. Vérifier le mot de passe avec `password_verify()`
5. Si OK → retourner un JWT contenant `id` et `email`
6. Si KO → retourner `401`

<details>
<summary>💡 Indice 1 — Lire le body JSON d'un POST</summary>

```php
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
```

</details>

<details>
<summary>💡 Indice 2 — Chercher un user par email (requête préparée)</summary>

```php
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();
```

</details>

<details>
<summary>💡 Indice 3 — password_verify()</summary>

```php
if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
    // OK → générer le JWT
}
```

</details>

<details>
<summary>✅ Solution complète</summary>

```php
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée"]);
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';

$database = new Database();
$conn = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$mot_de_passe = $data['mot_de_passe'] ?? '';

if (!$email || !$mot_de_passe) {
    http_response_code(400);
    echo json_encode(["erreur" => "Email et mot de passe requis"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
    $token = generer_jwt([
        'id' => $user['id'],
        'email' => $user['email']
    ]);
    echo json_encode([
        "token" => $token,
        "user" => [
            "id" => $user['id'],
            "nom" => $user['nom'],
            "prenom" => $user['prenom'],
            "email" => $user['email']
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(["erreur" => "Email ou mot de passe incorrect"]);
}
```

**Teste :**

```bash
curl -X POST http://localhost:8080/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@mail.com","mot_de_passe":"admin000"}'
```

</details>

---

# ÉTAPE 8 — Protéger les endpoints avec JWT

**But :** `users.php` et `produits.php` doivent refuser l'accès sans token valide.

### Exercice 8.1 — Modifie `api/users.php` et `api/produits.php`

Ajoute au début de chaque fichier :
1. Inclure `middleware/auth.php`
2. Appeler `get_user_from_token()`
3. Si `null` → `401` + `{"erreur": "Non autorisé"}`
4. Sinon → continuer normalement

<details>
<summary>💡 Indice — Le pattern de protection</summary>

```php
require_once __DIR__ . '/middleware/auth.php';

$user = get_user_from_token();
if (!$user) {
    http_response_code(401);
    echo json_encode(["erreur" => "Non autorisé"]);
    exit;
}
```

</details>

### Exercice 8.2 — Teste

```bash
# Sans token → 401
curl http://localhost:8080/api/users.php

# Login pour récupérer le token
TOKEN=$(curl -s -X POST http://localhost:8080/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@mail.com","mot_de_passe":"admin000"}' \
  | php -r 'echo json_decode(fgets(STDIN))->token;')

# Avec token → données
curl http://localhost:8080/api/users.php -H "Authorization: Bearer $TOKEN"
```

---

# ÉTAPE 9 — Le Frontend

**But :** Une page HTML simple avec 2 états : formulaire de login ↔ boutons d'action.

### Exercice 9.1 — Crée `front/index.html`

La page doit avoir **2 sections** (une seule visible à la fois) :

**Non connecté :**
```
┌───────────────────────────┐
│  Veuillez vous connecter  │
│  Email:    [___________]  │
│  Mot de passe: [______]   │
│  [Se connecter]           │
└───────────────────────────┘
```

**Connecté :**
```
┌───────────────────────────┐
│  Bonjour, {prenom} !      │
│  [Get Users] [Get Produits]│
│  [Se déconnecter]         │
│  ┌───────────────────────┐│
│  │ résultats en tableau  ││
│  └───────────────────────┘│
└───────────────────────────┘
```

**Règles :**
- Le token JWT est stocké dans `localStorage`
- Au chargement → vérifier si un token existe → afficher la bonne section
- Les boutons font un `fetch` avec le header `Authorization: Bearer xxx`
- "Se déconnecter" supprime le token du `localStorage`

<details>
<summary>💡 Indice 1 — 2 sections, une cachée</summary>

```html
<div id="login-section">
    <!-- formulaire -->
</div>
<div id="app-section" style="display:none;">
    <!-- boutons + résultats -->
</div>
```

</details>

<details>
<summary>💡 Indice 2 — Vérifier au chargement</summary>

```javascript
window.onload = function() {
    const token = localStorage.getItem('token');
    if (token) {
        document.getElementById('login-section').style.display = 'none';
        document.getElementById('app-section').style.display = 'block';
        document.getElementById('user-prenom').textContent = localStorage.getItem('prenom');
    }
};
```

</details>

<details>
<summary>💡 Indice 3 — Login avec fetch POST</summary>

```javascript
async function login() {
    const email = document.getElementById('email').value;
    const mdp = document.getElementById('mdp').value;

    const res = await fetch('/api/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, mot_de_passe: mdp })
    });
    const data = await res.json();

    if (data.token) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('prenom', data.user.prenom);
        location.reload();
    } else {
        alert(data.erreur);
    }
}
```

</details>

<details>
<summary>💡 Indice 4 — Fetch avec header Authorization</summary>

```javascript
async function getUsers() {
    const token = localStorage.getItem('token');
    const res = await fetch('/api/users.php', {
        headers: { 'Authorization': 'Bearer ' + token }
    });

    if (res.status === 401) { logout(); return; }

    const data = await res.json();
    afficherTableau(data);
}
```

</details>

<details>
<summary>💡 Indice 5 — Générer un tableau HTML dynamiquement</summary>

```javascript
function afficherTableau(data) {
    if (data.length === 0) { resultats.innerHTML = '<p>Vide</p>'; return; }

    let html = '<table><tr>';
    Object.keys(data[0]).forEach(k => html += '<th>' + k + '</th>');
    html += '</tr>';
    data.forEach(row => {
        html += '<tr>';
        Object.values(row).forEach(v => html += '<td>' + v + '</td>');
        html += '</tr>';
    });
    html += '</table>';
    document.getElementById('resultats').innerHTML = html;
}
```

</details>

<details>
<summary>✅ Solution complète</summary>

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PHP Learn API</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 50px auto; padding: 20px; background: #1a1a2e; color: #eee; }
        .box { background: #16213e; padding: 30px; border-radius: 10px; }
        input { display: block; width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #333; border-radius: 5px; background: #0f3460; color: #eee; font-size: 16px; box-sizing: border-box; }
        button { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; background: #e94560; color: white; }
        button:hover { background: #c73652; }
        .btn-logout { background: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #333; }
        th { color: #e94560; }
        .error { color: #e94560; }
    </style>
</head>
<body>
    <div class="box">
        <h1 style="text-align:center; color:#e94560;">PHP MySQL API</h1>

        <!-- LOGIN -->
        <div id="login-section">
            <h2>Veuillez vous connecter</h2>
            <input type="email" id="email" placeholder="Email">
            <input type="password" id="mdp" placeholder="Mot de passe">
            <button onclick="login()">Se connecter</button>
            <p id="login-error" class="error"></p>
        </div>

        <!-- APP -->
        <div id="app-section" style="display:none;">
            <p>Bonjour, <strong id="user-prenom"></strong> !</p>
            <button onclick="getUsers()">Get Users</button>
            <button onclick="getProduits()">Get Produits</button>
            <button class="btn-logout" onclick="logout()">Se déconnecter</button>
            <div id="resultats"></div>
        </div>
    </div>

    <script>
        // Au chargement
        window.onload = function() {
            const token = localStorage.getItem('token');
            if (token) {
                document.getElementById('login-section').style.display = 'none';
                document.getElementById('app-section').style.display = 'block';
                document.getElementById('user-prenom').textContent = localStorage.getItem('prenom') || 'Utilisateur';
            }
        };

        // Login
        async function login() {
            const email = document.getElementById('email').value;
            const mdp = document.getElementById('mdp').value;

            const res = await fetch('/api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, mot_de_passe: mdp })
            });
            const data = await res.json();

            if (data.token) {
                localStorage.setItem('token', data.token);
                localStorage.setItem('prenom', data.user.prenom);
                location.reload();
            } else {
                document.getElementById('login-error').textContent = data.erreur || 'Erreur';
            }
        }

        // Logout
        function logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('prenom');
            location.reload();
        }

        // Get Users
        async function getUsers() {
            const token = localStorage.getItem('token');
            const res = await fetch('/api/users.php', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            if (res.status === 401) { logout(); return; }
            const data = await res.json();
            afficherTableau(data);
        }

        // Get Produits
        async function getProduits() {
            const token = localStorage.getItem('token');
            const res = await fetch('/api/produits.php', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            if (res.status === 401) { logout(); return; }
            const data = await res.json();
            afficherTableau(data);
        }

        // Afficher un tableau
        function afficherTableau(data) {
            const el = document.getElementById('resultats');
            if (!data || data.length === 0) { el.innerHTML = '<p>Aucune donnée</p>'; return; }

            let html = '<table><tr>';
            Object.keys(data[0]).forEach(k => html += '<th>' + k + '</th>');
            html += '</tr>';
            data.forEach(row => {
                html += '<tr>';
                Object.values(row).forEach(v => html += '<td>' + v + '</td>');
                html += '</tr>';
            });
            html += '</table>';
            el.innerHTML = html;
        }
    </script>
</body>
</html>
```

</details>

---

# ÉTAPE 10 — Test final

### Checklist

```bash
# 1. Crée la base MySQL
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS php_learn;"

# 2. Lance le serveur PHP (depuis le dossier PHP_MYSQL_API/)
php -S localhost:8080

# 3. Seed la base → ouvre dans le navigateur :
#    http://localhost:8080/api/seed.php
#    → {"message":"Seed terminé","users":4,"produits":8}

# 4. Ouvre le frontend :
#    http://localhost:8080/front/index.html

# 5. Connecte-toi : admin@mail.com / admin000
# 6. Clique "Get Users" → tableau sans mots de passe
# 7. Clique "Get Produits" → tableau des produits
# 8. Clique "Se déconnecter" → retour au formulaire
# 9. Teste un mauvais mot de passe → message d'erreur
```

---

# Récap des syntaxes PHP apprises

| Concept | Syntaxe |
|---|---|
| Variable | `$nom = "valeur";` |
| Tableau associatif | `$arr = ["clé" => "valeur"];` |
| Classe | `class Foo { }` |
| Propriété | `private $bar = "val";` |
| Méthode | `public function baz() { }` |
| $this | `$this->propriete` |
| Inclure fichier | `require_once __DIR__ . '/file.php';` |
| Constante | `define('NOM', 'valeur');` |
| PDO connexion | `new PDO($dsn, $user, $pass)` |
| Requête préparée | `$conn->prepare("... :param")` |
| Execute avec params | `$stmt->execute([':param' => $val])` |
| Fetch un résultat | `$stmt->fetch()` |
| Fetch tous | `$stmt->fetchAll()` |
| Lire fichier | `file_get_contents('path')` |
| Lire body POST | `file_get_contents('php://input')` |
| JSON → PHP | `json_decode($str, true)` |
| PHP → JSON | `json_encode($data)` |
| Hash mot de passe | `password_hash($mdp, PASSWORD_DEFAULT)` |
| Vérifier mot de passe | `password_verify($mdp, $hash)` |
| Header HTTP | `header('Content-Type: application/json')` |
| Code HTTP | `http_response_code(401)` |
| Méthode HTTP | `$_SERVER['REQUEST_METHOD']` |
| Headers reçus | `getallheaders()` |
| HMAC signature | `hash_hmac('sha256', $data, $secret, true)` |
| foreach | `foreach ($arr as $item) { }` |
| explode | `explode('.', $string)` |
| substr | `substr($str, 7)` |
| Null coalescing | `$val = $arr['clé'] ?? null;` |
| try/catch | `try { } catch (PDOException $e) { }` |
| exit | `exit;` |

---

# Bonus — Pour aller plus loin

- [ ] Ajouter `POST /api/produits.php` pour créer un produit
- [ ] Ajouter `DELETE /api/produits.php?id=3` pour supprimer
- [ ] Ajouter de la pagination (`?page=1&limit=10`)
- [ ] Ajouter un rôle `admin` et restreindre certains endpoints
- [ ] Mettre la clé JWT et le mot de passe DB dans un fichier `.env`
