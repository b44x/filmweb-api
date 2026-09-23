# Changelog

Format: [Keep a Changelog](https://keepachangelog.com/pl/1.1.0/), wersjonowanie: [SemVer](https://semver.org/lang/pl/).

## [2.0.0] – niewydane

Całkowite przepisanie biblioteki. Wersja **nie jest wstecznie kompatybilna** – zobacz [UPGRADE.md](UPGRADE.md).

### Dodano
- Obsługa REST API `https://www.filmweb.pl/api/v1` (stare mobilne API `ssl.filmweb.pl/api` przestało działać).
- Zasoby: `films` (`get`, `info`, `preview`, `description`, `rating`, `criticsRating`, `dates`, `topRoles`, `cast`),
  `people` (`get`), `vod` (`providers`, `offers`) oraz `search()`, `call()` i `raw()`.
- Obsługa seriali (te same endpointy) i kanoniczne linki `TitleInfo::url()`.
- Dekoratory transportu: `RetryingTransport` (429/5xx, exponential backoff) i `CachingTransport` (PSR-16).
- Value object `Image` (CDN fwcdn.pl, wybór rozmiaru), niemutowalne modele `readonly`, enumy `TitleType`, `ImageKind`.
- Wymaganie PHP `^8.2`, `declare(strict_types=1)`, autoloading PSR-4 (`NSolutions\Filmweb\`).
- `CurlTransport` i `Psr18Transport`, hierarchia wyjątków `FilmwebException`.
- Testy (PHPUnit 11), PHPStan (level max + strict rules), PHP-CS-Fixer (PER-CS 2.0), GitHub Actions.

### Usunięto
- Metody starego API bez odpowiednika w nowym: `Login`, `getUserFilmVotes`, `getFilmComments`,
  `getFilmImages`, `getFilmVideos`, `getFilmReview` (można je dodać jako własne endpointy, gdy zostaną zidentyfikowane).
- Singleton `Filmweb::instance()`, magiczne `__call()`, fluent `->execute()` i globalny, mutowalny stan.
- Wyłączona weryfikacja certyfikatów TLS.

## [1.0.0] – 2013

Wersja legacy (PHP 5.4, mobilne API), dostępna pod tagiem [`v1.0.0`](https://github.com/b44x/filmweb-api/tree/v1.0.0). Nie jest już rozwijana i nie działa z obecnym Filmwebem.
