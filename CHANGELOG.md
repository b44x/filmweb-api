# Changelog

Wszystkie istotne zmiany w projekcie. Format: [Keep a Changelog](https://keepachangelog.com/pl/1.1.0/),
wersjonowanie: [Semantic Versioning](https://semver.org/lang/pl/). Zasady zgodności wstecznej: [README](README.md#wersjonowanie).

## [Unreleased]

## [2.0.0-beta.1] – 2026-09-23

Całkowite przepisanie biblioteki na nowe REST API Filmweb. Wydanie beta – API Filmweb jest nieoficjalne,
a publiczne API biblioteki może się jeszcze zmienić przed `2.0.0`. Migracja: [UPGRADE.md](UPGRADE.md).

### ⚠️ Zmiany niekompatybilne (BC breaks)
- **Źródło danych:** mobilne API `ssl.filmweb.pl/api` (wyłączone przez Filmweb) zastąpione REST API `www.filmweb.pl/api/v1`.
- **Wymagania:** PHP `>=5.4` → `^8.2`; nowe zależności: `psr/http-client`, `psr/http-factory`, `psr/simple-cache` (same interfejsy).
- **Namespace i autoloading:** `nSolutions\…` (PSR-0) → `NSolutions\Filmweb\…` (PSR-4).
- **Punkt wejścia:** singleton `Filmweb::instance()`, magiczne `__call()` i `->execute()` → `Filmweb::create()`
  z zasobami `films`, `people`, `vod` oraz metodami `search()`, `call()`, `raw()`.
- **Zwracane dane:** `stdClass` / `false` → typowane modele `readonly`, `null` lub pusta lista (HTTP 404).
- **Usunięte metody** bez odpowiednika w nowym API: `Login`, `getUserFilmVotes`, `getFilmComments`, `getFilmImages`,
  `getFilmVideos`, `getFilmReview`.
- **Usunięty globalny stan:** `Filmweb::$_config`, `Filmweb::$genres`, `Filmweb::$roles`, `Request::$default_options`.
- **Błędy:** ogólny `\Exception` → hierarchia `FilmwebException`.
- **TLS:** weryfikacja certyfikatów jest włączona (1.x ją wyłączała).
- **Licencja:** ujednolicona do MIT (wcześniej niespójnie: MIT w `composer.json`, ISC w `LICENSE.md`, CC BY 3.0 w README).

### Dodano
- Wyszukiwarka `search()` oraz zasoby:
  - `films`: `get` (info + preview + oceny + daty), `info`, `preview`, `description`, `rating`, `criticsRating`, `dates`, `topRoles`, `cast`,
  - `people`: `get`,
  - `vod`: `providers`, `offers` (aktywne oferty z cenami w PLN).
- Obsługa seriali tymi samymi endpointami oraz kanoniczne linki `TitleInfo::url()`.
- Własne endpointy przez interfejs `Endpoint` / klasę bazową `FilmEndpoint` i `Filmweb::call()`; surowy dostęp `raw()`.
- Transporty `CurlTransport` i `Psr18Transport` oraz dekoratory `RetryingTransport` (429/5xx) i `CachingTransport` (PSR-16).
- Value object `Image` (CDN fwcdn.pl, wybór rozmiaru), enumy `TitleType`, `ImageKind`.
- Testy (PHPUnit 11), PHPStan (level max + strict rules), PHP-CS-Fixer (PER-CS 2.0), CI na PHP 8.2–8.5,
  automatyczne wydania (GitHub Release z CHANGELOG).

## [1.0.0] – 2018-07-16

Wersja legacy (PHP 5.4, mobilne API Filmweb). **Nie działa** – Filmweb wyłączył to API. Nierozwijana,
dostępna na gałęzi [`1.x`](https://github.com/b44x/filmweb-api/tree/1.x).

[Unreleased]: https://github.com/b44x/filmweb-api/compare/v2.0.0-beta.1...HEAD
[2.0.0-beta.1]: https://github.com/b44x/filmweb-api/compare/v1.0.0...v2.0.0-beta.1
[1.0.0]: https://github.com/b44x/filmweb-api/releases/tag/v1.0.0
