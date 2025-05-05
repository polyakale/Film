# Kezdő Útmutató: Vue.js és Laravel Projektek Elindítása

Ez az útmutató segít elindítani a megadott **Vue.js** (Vite alapú) és **Laravel** projekteket, még akkor is, ha nincs előzetes tapasztalatod.

---

## 1. Előfeltételek – Szükséges Szoftverek Telepítése

Győződj meg róla, hogy a következő szoftverek telepítve vannak a számítógépeden:

### Node.js és npm
A Vue.js projekthez szükséges. Az `npm` (Node Package Manager) a Node.js-sel együtt települ.  
Letöltés: [https://nodejs.org/](https://nodejs.org/) (ajánlott: LTS verzió)

Telepítés ellenőrzése:
```bash
node -v
npm -v
```

### PHP
A Laravel keretrendszerhez szükséges.  
Letöltés: [https://www.php.net/downloads.php](https://www.php.net/downloads.php)  
Ajánlott csomagok: **XAMPP**, **Laragon**, **WAMP**, **MAMP** (PHP + MySQL + Apache egyben)

Ellenőrzés:
```bash
php -v
```

### Composer
PHP függőségkezelő, Laravel-hez elengedhetetlen.  
Letöltés: [https://getcomposer.org/](https://getcomposer.org/)

Ellenőrzés:
```bash
composer -V
```

### Visual Studio Code (VS Code)
Letöltés: [https://code.visualstudio.com/](https://code.visualstudio.com/)

Telepítsd a **Volar** kiegészítőt (és kapcsold ki a Vetur-t, ha telepítve van).

### Git
Verziókövető rendszer.  
Letöltés: [https://git-scm.com/](https://git-scm.com/)

---

## 2. Vue.js Projekt Elindítása

Ez a projekt a **frontend**, azaz a felhasználói felület.

### Projekt letöltése
Ha Git repository-ból dolgozol:
```bash
git clone <repository_url>
```

Egyébként csomagold ki a fájlokat egy saját mappába.

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
Ezután megjelenik például: `http://localhost:5173/`, amit megnyithatsz böngészőben.

### Leállítás
A terminálban:
<kbd>Ctrl</kbd> + <kbd>C</kbd>

### Hasznos további parancsok

| Parancs             | Leírás                                    |
| ------------------- | ----------------------------------------- |
| `npm run build`     | Éles, optimalizált verzió (dist mappa)    |
| `npm run preview`   | Build utáni helyi megtekintés             |
| `npm run test:unit` | Egységtesztek futtatása, ha van beállítva |

---

## 3. Laravel Projekt (`hungarianfilms`) Elindítása

Ez a projekt a **backend**, vagyis az adatkezelés és API rész.

### Projekt létrehozása vagy megnyitása
Ha új projektet hoznál létre:
```bash
composer create-project laravel/laravel hungarianfilms
```

Ha már megvan a projekt:
```bash
cd path/to/your/hungarianfilms
```

### Függőségek telepítése
```bash
composer install
```

### `.env` fájl létrehozása
```bash
cp .env.example .env  # macOS/Linux
copy .env.example .env  # Windows
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
Hozz létre egy `hungarianfilms` nevű adatbázist MySQL-ben (például phpMyAdmin-ban):
```sql
CREATE DATABASE `hungarianfilms` CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
```

### `.env` adatbázis konfiguráció
Például:
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
Ha van saját init parancs:
```bash
php artisan db:initdb
```

### Laravel szerver indítása
```bash
php artisan serve
```
Megnyitható a böngészőben: `http://127.0.0.1:8000` vagy `http://localhost:8000`

---

## 4. Tesztelés

### Vue.js tesztek
```bash
npm run test:unit
```

### Laravel tesztek
```bash
php artisan test
```

Ha külön teszt adatbázist használsz:
```bash
APP_ENV=testing php artisan db:initdb
```

---

## 5. További Források

- [Laravel dokumentáció](https://laravel.com/docs)
- [Vue 3 dokumentáció](https://vuejs.org/guide/introduction.html)

---