# Kezdő Útmutató: Vue.js és Laravel Projektek Elindítása

Ez az útmutató segít elindítani a megadott Vue.js (Vite alapú) és Laravel projekteket, még akkor is, ha nincs előzetes tapasztalatod.

## 1. Előfeltételek: Szükséges Szoftverek Telepítése

Mielőtt elkezdenéd, győződj meg róla, hogy a következő szoftverek telepítve vannak a számítógépeden:

- **Node.js és npm**  
  A Vue.js projekthez szükséges. Az npm (Node Package Manager) a Node.js-sel együtt települ.  
  Letöltődik innen: [Node.js](https://nodejs.org/) (az LTS verzió ajánlott).  
  Telepítés után nyiss egy terminált vagy parancssort, és ellenőrizd a telepítést:
  ```bash
  node -v
  npm -v
  ```

- **PHP**  
  A Laravel keretrendszer futtatásához szükséges.  
  Letöltődik innen: [PHP Downloads](https://www.php.net/downloads.php) vagy használj egy csomagkezelőt (pl. XAMPP, WAMP, MAMP, Laragon), ami tartalmazza a PHP-t, webszervert (Apache/Nginx) és adatbázis-kezelőt (MySQL/MariaDB).  
  Ellenőrizd a telepítést:
  ```bash
  php -v
  ```

- **Composer**  
  A PHP függőségkezelője, ami a Laravel projekthez szükséges.  
  Letöltődik innen: [Composer](https://getcomposer.org/)  
  Ellenőrizd a telepítést:
  ```bash
  composer -V
  ```

- **Kódszerkesztő**  
  (Ajánlott: [Visual Studio Code](https://code.visualstudio.com/)).  
  Egy jó kódszerkesztő megkönnyíti a munkát.  
  Vue.js fejlesztéshez telepítsd a Volar kiegészítőt a VS Code-ban (és kapcsold ki a Vetur kiegészítőt, ha telepítve van).

- **Git** (Opcionális, de erősen ajánlott)  
  Verziókövető rendszer, hasznos a kód kezeléséhez és a projektek letöltéséhez.  
  Letöltődik innen: [Git SCM](https://git-scm.com/)

---

## 2. Vue.js Projekt ("films") Beállítása és Elindítása

Ez a projekt a frontend (felhasználói felület) részt kezeli.

1. **Projekt Letöltése/Klónozása:**  
   - Ha a projekt egy Git repository-ban van, klónozd le:  
     ```bash
     git clone <repository_url>
     ```
   - Ha csak a fájlokat kaptad meg, csomagold ki egy tetszőleges mappába.

2. **Navigálj a Projekt Mappájába:**  
   Nyiss egy terminált vagy parancssort, majd lépj be a projekt főmappájába, ahol a `package.json` található. Például:
   ```bash
   cd path/to/your/films
   ```

3. **Függőségek Telepítése:**  
   Futtasd a következő parancsot az ajánlott csomagok letöltéséhez:
   ```bash
   npm install
   ```

4. **Fejlesztői Szerver Indítása:**  
   Indítsd el a fejlesztői szervert, amely automatikusan frissül, ha módosítod a kódot:
   ```bash
   npm run dev
   ```
   A terminál valószínűleg megadja az URL-t (általában például: `http://localhost:5173/`), ahol a böngésződben megtekintheted az alkalmazást.

5. **Alkalmazás Leállítása:**  
   A terminálban nyomd meg a <kbd>Ctrl</kbd> + <kbd>C</kbd> billentyűkombinációt.

6. **További Vue.js Parancsok:**  
   - **Build:**  
     ```bash
     npm run build
     ```
     Létrehozza az alkalmazás optimalizált, éles környezetbe szánt verzióját (általában egy `dist` mappába).
   - **Preview:**  
     ```bash
     npm run preview
     ```
     Az `npm run build` után futtatva, megtekintheted a buildelt verziót helyben.
   - **Egységtesztek:**  
     ```bash
     npm run test:unit
     ```
     (Ha vannak definiálva egységtesztek a projektben.)

---

## 3. Laravel Projekt ("hungarianfilms") Beállítása és Elindítása

Ez a projekt a backend (szerveroldali logika, adatbázis-kezelés, API) részt kezeli.

1. **Projekt Létrehozása:**  
   - Ha teljesen új projektet kell létrehoznod:
     ```bash
     composer create-project laravel/laravel hungarianfilms
     ```
   - Ha már megvan a projekt (letöltötted, klónoztad), lépj be a projekt mappájába:
     ```bash
     cd path/to/your/hungarianfilms
     ```

2. **Függőségek Telepítése:**  
   Ha nem a `create-project` paranccsal hoztad létre, futtasd:
   ```bash
   composer install
   ```

3. **Konfigurációs Fájl Létrehozása:**  
   A Laravel az `.env` fájlban tárolja a környezeti beállításokat (adatbázis, email stb.). Másold le az `.env.example` fájlt `.env` néven:
   - Linux/macOS:
     ```bash
     cp .env.example .env
     ```
   - Windows:
     ```bash
     copy .env.example .env
     ```

4. **Alkalmazás Kulcs Generálása:**  
   Ez a biztonsághoz szükséges:
   ```bash
   php artisan key:generate
   ```

5. **API Telepítése (ha szükséges):**  
   Ha a projekt kizárólag API-ként funkcionál (nincs hagyományos webes felülete), futtasd:
   ```bash
   php artisan install:api
   ```

6. **Adatbázis Beállítása:**

   - **Adatbázis Létrehozása:**  
     Hozz létre egy adatbázist a MySQL szervereden (például phpMyAdmin segítségével vagy a parancssorból). Az alábbi parancs egy `hungarianfilms` nevű adatbázist hoz létre, `utf8_hungarian_ci` karakterkódolással:
     ```sql
     CREATE DATABASE `hungarianfilms` CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
     ```

   - **.env Fájl Szerkesztése:**  
     Nyisd meg az `.env` fájlt, és módosítsd az adatbázis kapcsolati adatokat (a `DB_` kezdetű sorokat). Példa:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=hungarianfilms  # Ezt a nevet adtad az adatbázisnak
     DB_USERNAME=root            # Általában 'root' használatos helyi fejlesztésnél
     DB_PASSWORD=                # Add meg a MySQL root jelszavad, ha van
     ```

7. **Adatbázis Táblák Létrehozása (Migráció):**  
   Ez a parancs létrehozza a projekt által definiált táblákat:
   ```bash
   php artisan migrate
   ```

8. **Adatbázis Feltöltése Tesztadatokkal (Seeding):**  
   Ha a projekt tartalmaz ún. "seedereket" (tesztadatokat generáló osztályokat), ezzel töltheted fel a táblákat:
   ```bash
   php artisan db:seed
   ```
   Ha létezik a projektben egy saját parancs, például `db:initdb`, akkor ezt is futtathatod:
   ```bash
   php artisan db:initdb
   ```

9. **Fejlesztői Szerver Indítása:**  
   Indítsd el a Laravel beépített szerverét:
   ```bash
   php artisan serve
   ```
   A terminál általában kiírja az URL-t (például: [http://127.0.0.1:8000](http://127.0.0.1:8000)), ahol elérheted az API végpontokat (pl. [http://127.0.0.1:8000/api/](http://127.0.0.1:8000/api/)).

10. **Alkalmazás Leállítása:**  
    A terminálban nyomd meg a <kbd>Ctrl</kbd> + <kbd>C</kbd> billentyűkombinációt.

---

## 4. Tesztelés

Mindkét projekt tartalmazhat automatizált teszteket a kód helyes működésének ellenőrzésére.

- **Vue.js Tesztek Futtatása:**
  ```bash
  npm run test:unit
  ```

- **Laravel Tesztek Futtatása:**
  ```bash
  php artisan test
  ```
  A Laravel tesztekhez akár egy külön tesztadatbázist is használhatsz (az `.env.testing` fájl beállításai alapján), hogy ne befolyásold az éles fejlesztői adatbázist. Ha szükséges és be van állítva, a teszt adatbázis inicializálásához:
  ```bash
  APP_ENV=testing php artisan db:initdb
  ```
---
Ezzel az útmutatóval készen állsz a Vue.js és Laravel (hungarianfilms) projektek alapvető beállítására és elindítására. Sok sikert!

# Roadmap / To-Do List

- [x] **Completing Home page.**
- [ ] **Have all Tests done for all tables in backend.**
- [ ] **Possible Frontend test.**
- [ ] **Have the admin-only dropdown menu to access all tables in a table form for each entity.**
- [ ] **Make separate components for "My Reviews" and "All Reviews".**
- [ ] **Refine "Show more / Show less" functionality: limit output to two lines with a focus on usability and error prevention.**
- [ ] **Make "My Reviews" responsive (similar to "All Reviews", with its own tweaks).**
- [ ] **Modify the Search and Filter interface: opt for a side panel instead of a dropdown menu.**
- [ ] **Ensure the styles for People & Films align uniformly with the overall website design.**
- [ ] **Enhance the README: add a guide for the installation of the "Vizsgaremek".**
