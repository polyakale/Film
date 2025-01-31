# Adatbázis
## Diagarm

![diagram](Documents/diagram.png)

## positions table (Pozíció)
- Ebben a táblázatban találhatóak meg a usereknek a "poziciója" azaz hogy a user milyen szerepet tölt be: Admin vagy Guest
- id
- name (a poziciójának a megnevezése) admin avagy nem (guest)

## users (felhasználók)
- id
- name (név: a felhasználó neve)
- positionId (pozicioId: ezt a positions-ból kapja meg)

## films (filmek)
- id
- title (cím: a filmnek a címe)
- production (gyártás: a film forgatásának kezdési éve)
- lenght (hossz: a filmnek a hossza)
- presentation (bemutato: a film kiadási éve)
- imdbLink (a filmhez tartozó link az imdb-ről)

## videos (videók)
- id
- filmId (ezt a films táblázatból kapja meg)
- link (YouTube-on a linkje, de nem biztos hogy van minden filmhez)
- embedLink (beágyazásiLink: Ez a beágyazási linkje persze ez csak akkor van ha fent van a YouTube-on)

## favourites (kedvencek)
(a felhasználóknak a kedvenc filmjei itt lesznek találhatóak)
- id
- userId (ezt a users táblázatból kapja meg)
- filmId (ezt a films táblázatból kapja meg)
- evaluation (ertekeles: a filmre adott értékelése)

## roles (szerepkörök)
- id
- role (szerepkor: a szerepkör megnevezése(színész, operatőr...stb))

## people (emberek(színészek, rendezők...stb))
- id
- name (a személy neve)
- gender (neme: a személynek a neme)
- photo (foto: fotó az illetőről, ha van)
- imdbLink (igazából itt meg ha van leírás a személyról akkor a linket találhatod meg itt)

## tasks (feladatok)
- id
- filmId (ezt a films táblázatból kapja meg)
- personId (szemelyId: ezt a people táblázatból kapja meg)
- roleId (szerepkorId: ezt a roles táblázatból kapja meg)