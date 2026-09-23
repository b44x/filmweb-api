# Changelog

Format: [Keep a Changelog](https://keepachangelog.com/pl/1.1.0/), wersjonowanie: [SemVer](https://semver.org/lang/pl/).

## [2.0.0] – niewydane

Całkowite przepisanie biblioteki. Wersja **nie jest wstecznie kompatybilna** – zobacz [UPGRADE.md](UPGRADE.md).

### Dodano
- Obsługa nowego REST API `https://www.filmweb.pl/api/v1` (stare mobilne API `ssl.filmweb.pl/api` przestało działać).
- Wyszukiwarka (`search()`), zbiorcze `film()`, osobne `info()`, `preview()` (gatunki, kraje, czas trwania, plakat), `description()`,
  `rating()` (z rozkładem głosów), `criticsRating()`, `dates()` (premiery).
- Obsada: `topRoles()`, `topCast()` oraz osoby: `person()`.
- Gdzie obejrzeć: `whereToWatch()` (aktywne oferty VOD z cenami) i `vodProviders()`.
- Surowy dostęp do dowolnej ścieżki API: `raw()`; własne endpointy przez `call()`.
- Wymaganie PHP `^8.2`, `declare(strict_types=1)` i pełne typowanie; autoloading PSR-4 (`NSolutions\Filmweb\`).
- Niemutowalne modele (`readonly`): `Film`, `TitleInfo`, `Preview`, `Genre`, `Rating`, `ReleaseDates`, `TopRole`, `CastMember`,
  `Person`, `PersonRef`, `VodProvider`, `VodOffer`, `SearchHit`; enum `TitleType`.
- Warstwa transportu `Transport` z implementacjami `CurlTransport` i `Psr18Transport`.
- Hierarchia wyjątków implementujących `FilmwebException`.
- Testy (PHPUnit 11), PHPStan (level max + strict rules), PHP-CS-Fixer (PER-CS 2.0), GitHub Actions.

### Usunięto
- Metody starego API bez odpowiednika w nowym: `Login`, `getUserFilmVotes`, `getFilmComments`,
  `getFilmImages`, `getFilmVideos`, `getFilmReview` (można je dodać jako własne endpointy, gdy zostaną zidentyfikowane).
- Singleton `Filmweb::instance()`, magiczne `__call()`, fluent `->execute()` i globalny, mutowalny stan.
- Wyłączona weryfikacja certyfikatów TLS.

## [1.0.0] – 2013

Wersja legacy (PHP 5.4, mobilne API), dostępna pod tagiem [`v1.0.0`](https://github.com/b44x/filmweb-api/tree/v1.0.0). Nie jest już rozwijana i nie działa z obecnym Filmwebem.
