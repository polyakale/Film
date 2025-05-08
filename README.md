# Kezdő Útmutató: Vue.js és Laravel Projektek Elindítása

Ez az útmutató segít elindítani a megadott **Vue.js** (Vite alapú) és **Laravel** projekteket, még akkor is, ha nincs előzetes tapasztalatod.

---

## 1. Előfeltételek – Szükséges Szoftverek Telepítése

Győződj meg róla, hogy a következő szoftverek telepítve vannak a számítógépeden:

### Node.js és npm
- **Leírás:** A Vue.js projekthez szükséges. Az `npm` (Node Package Manager) a Node.js-sel együtt települ.
- **Letöltés:** [https://nodejs.org/](https://nodejs.org/) (ajánlott: LTS verzió)
- **Telepítés ellenőrzése:**
  ```bash
  node -v
  npm -v
  ```

### PHP
- **Leírás:** A Laravel keretrendszerhez szükséges.
- **Letöltés:** [https://www.php.net/downloads.php](https://www.php.net/downloads.php)  
  **Ajánlott csomagok:** XAMPP, Laragon, WAMP, MAMP (PHP + MySQL + Apache egyben)
- **Telepítés ellenőrzése:**
  ```bash
  php -v
  ```

### Composer
- **Leírás:** PHP függőségkezelő, Laravel-hez elengedhetetlen.
- **Letöltés:** [https://getcomposer.org/](https://getcomposer.org/)
- **Telepítés ellenőrzése:**
  ```bash
  composer -V
  ```

### Visual Studio Code (VS Code)
- **Letöltés:** [https://code.visualstudio.com/](https://code.visualstudio.com/)  
- **Extra:** Telepítsd a Volar kiegészítőt (és kapcsold ki a Vetur-t, ha telepítve van).

### Git
- **Leírás:** Verziókövető rendszer.
- **Letöltés:** [https://git-scm.com/](https://git-scm.com/)

---

## 2. Vue.js Projekt Elindítása
Ez a projekt a **frontend** (felhasználói felület) részt kezeli.

### Projekt letöltése
- **Ha Git repository-ból dolgozol:**
  ```bash
  git clone <repository_url>
  ```
- **Egyébként:** Csomagold ki a fájlokat egy saját mappába.

### Lépj be a projekt mappájába
```bash
cd path/to/your/films
```

### Függőségek telepítése
```bash
npm install
```

### Fejlesztői szerver indítása
```bash
npm run dev
```
- **Megjelenik példa URL:** `http://localhost:5173/`

### Leállítás
- **Terminálban:**  
  `<kbd>Ctrl</kbd> + <kbd>C</kbd>`

### Hasznos további parancsok

| Parancs              | Leírás                                          |
| -------------------- | ----------------------------------------------- |
| `npm run build`      | Éles, optimalizált verzió (`dist` mappa)         |
| `npm run preview`    | Build utáni helyi megtekintés                    |
| `npm run test:unit`  | Egységtesztek futtatása (ha van beállítva)         |

---

## 3. Laravel Projekt (`hungarianfilms`) Elindítása
Ez a projekt a **backend** részt kezeli (adatkezelés és API).

### Projekt létrehozása vagy megnyitása
- **Új projekt létrehozása:**
  ```bash
  composer create-project laravel/laravel hungarianfilms
  ```
- **Már meglévő projekt esetén:**
  ```bash
  cd path/to/your/hungarianfilms
  ```

### Függőségek telepítése
```bash
composer install
```

### `.env` fájl létrehozása
- **macOS/Linux:**
  ```bash
  cp .env.example .env
  ```
- **Windows:**
  ```bash
  copy .env.example .env
  ```

### Alkalmazáskulcs generálása
```bash
php artisan key:generate
```

### API telepítése (ha szükséges)
```bash
php artisan install:api
```

### Adatbázis létrehozása
- **Hozz létre egy `hungarianfilms` nevű adatbázist MySQL-ben (például phpMyAdmin-ban):**
  ```sql
  CREATE DATABASE `hungarianfilms` CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
  ```

### `.env` adatbázis konfiguráció
- **Szerkeszd a `.env` fájlt az alábbi példával:**
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=hungarianfilms
  DB_USERNAME=root
  DB_PASSWORD=
  ```

### Migráció – Táblák létrehozása
```bash
php artisan migrate
```

### Tesztadatok feltöltése (seeding)
```bash
php artisan db:seed
```
- **Ha van saját init parancs:**
  ```bash
  php artisan db:initdb
  ```

### Laravel szerver indítása
```bash
php artisan serve
```
- **Megnyitható URL-k:** `http://127.0.0.1:8000` vagy `http://localhost:8000`

---

## 4. Tesztelés

### Laravel Tesztek
```bash
php artisan test
```
- **Ha külön teszt adatbázist használsz (.env.testing):**
  ```bash
  APP_ENV=testing php artisan db:initdb
  php artisan test
  ```

---

## 5. További Források

- [Laravel dokumentáció](https://laravel.com/docs)
- [Vue 3 dokumentáció](https://vuejs.org/guide/introduction.html)

---

## 6. Playwright – Végponttól Végpontig (E2E) Frontend Tesztelés

A projekt Vue.js része támogatja a Playwright tesztelést (például bejelentkezés, navigáció, interakciók).

### Telepítés
```bash
npx playwright install
```

### Tesztek futtatása
```bash
npx playwright test
```

### Egy teszt vagy fájl futtatása
```bash
npx playwright test tests/auth.spec.js
npx playwright test tests/home.spec.js
```

### Eredmények megtekintése (HTML riport)
```bash
npx playwright show-report
```

### Fejlesztői mód
```bash
npx playwright test
```

**Tipp:** Győződj meg róla, hogy a fejlesztői szerver (`npm run dev`) fut a tesztelés alatt!

---

## 7. Laravel Feature Tesztek

A backend Laravel projekt Feature teszteket is tartalmaz (például autentikáció, CRUD műveletek).

### Tesztek futtatása
```bash
php artisan test
```

### Tesztfájlok
- Az egyéni tesztek általában a `tests/Feature/` mappában találhatók, például:  
 `tests/Feature/FavoritesTest.php`, stb.

Ha saját teszt adatbázist használsz (`.env.test` fájl), előfordulhat, hogy frissítened kell:
```bash
php artisan config:clear
php artisan migrate --env=test
php artisan db:seed --env=test
php artisan test
```
