# Amőba játék

![ci badge](https://github.com/dgabor222/projekt_eszkozok_2021/actions/workflows/ci.yml/badge.svg)

## Projekt eszközök beadandó

A tárgy során fejlesztett projektünk terveink szerint egy olyan webes alkalmazást valósít meg, amely lehetővé teszi, hogy amőba játékot játsszunk más felhasználókkal, vagy akár vendégekkel. Emellett biztosít minimális adminisztrációs eszközöket is.

## Funkcionális követelmények
- Szép, átlátható felhasználói felület
- Hitelesítés és jogosultságkezelés
	- Regisztráció és bejelentkezés
	- Szerepkörök megvalósítása
- Amőba játék
	- Két játékos ténylegesen le tudja játszani az amőba játékot
- Eredménytáblák
	- A játékok eredményétől függően eredménytábla készítése
- Profil beállítási lehetőségek
	- A felhasználók képesek legyenek testreszabni a profiljukat, pl. becenév/profilkép megadása.
	- A profilok megtekinthetőek legyenek, jelezze ki a felhasználó eredményeit is.
- Adminisztratív eszközök
	- Az adminisztrátori szerepkörrel rendelkező felhasználó számára álljon rendelkezésre egy dedikált felület, ahol alapvető műveleteket végezhet el, mint pl. felhasználó kitiltása.

## Nem funkcionális követelmények:
- Az alkalmazás legyen könnyen kezelhető és átlátható.

## Szerepkörök
Az alkalmazásban alapvetően három szerepkör lesz, amelyek a következők:
- Vendég
	- Regisztrálhat vagy bejelentkezhet
	- Megtekintheti az eredménytáblát
	- Ha meghívják játszani, egy link segítségével csatlakozhat
- Felhasználó
	- Megtekintheti/szerkesztheti a profilját, pl. megadhat nicknevet, vagy profilképet.
	- Játékot kezdeményezhet
- Admin
	- Kezelheti a felhasználókat (törlés, jelszó csere, kitiltás, stb.)

## Használt technológiák
A projekt megvalósítása érdekében az alábbi technológiákat tervezzük használni:
- Laravel 8 keretrendszer
- Bootstrap CSS keretrendszer
- React komponensek
- SQLite3 adatbázis

## Rendszerkövetelmény
- A tervezett technológiák függvényében a rendszerkövetelmények a következők lesznek.
- Szerver oldalon:
	- Windows/Linux OS
	- Legalább egy processzor mag
	- Legalább 512 MB RAM
	- Legalább 200 MB szabad tárhely, lehetőleg SSD
	- Legalább 100Mbps hálózati kapcsolat
- Kliens oldalon:
	- Modern böngésző, ami képes az újabb Javascript technológiák értelmezésére és végrehajtására.
		- Ajánlott: Google Chrome vagy a Chromium egyéb népszerű forkjai
	- Olyan számítógép, ami ezt a böngészőt tudja futtatni.
