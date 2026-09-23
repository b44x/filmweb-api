# Filmweb API for PHP

[![CI](https://github.com/b44x/filmweb-api/actions/workflows/ci.yml/badge.svg)](https://github.com/b44x/filmweb-api/actions/workflows/ci.yml)
![PHP](https://img.shields.io/badge/php-%5E8.2-777BB4?logo=php&logoColor=white)
![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen)
![Code style](https://img.shields.io/badge/code%20style-PER--CS%202.0-blue)
[![Packagist](https://img.shields.io/packagist/v/nsolutionspl/filmweb-api?include_prereleases)](https://packagist.org/packages/nsolutionspl/filmweb-api)
[![License](https://img.shields.io/github/license/b44x/filmweb-api)](LICENSE)

Nieoficjalny, w pełni typowany klient REST API [Filmweb.pl](https://www.filmweb.pl) (`https://www.filmweb.pl/api/v1`) –
tego samego, z którego korzysta strona filmweb.pl.

```php
$filmweb = Filmweb::create();

$hit  = $filmweb->search('matrix')[0];        // SearchHit { id: 628, title: "Matrix", type: "film" }
$film = $filmweb->films->get($hit->id);

echo "{$film->info->title} ({$film->info->year}) – ★ {$film->rating?->rounded()}";
// Matrix (1999) – ★ 7.6
```

> [!NOTE]
> To jest **2.x** – przepisana od zera wersja dla PHP 8.2+, oparta na REST API strony.
> Stare mobilne API (`ssl.filmweb.pl/api`), z którego korzystała wersja 1.x, **już nie działa**.
> Wersja legacy (PHP 5.4) jest dostępna jako [`v1.0.0`](https://github.com/b44x/filmweb-api/releases/tag/v1.0.0) / gałąź [`1.x`](https://github.com/b44x/filmweb-api/tree/1.x). Przechodzisz z 1.x? Zobacz [UPGRADE.md](UPGRADE.md).

> [!WARNING]
> API nie jest oficjalnie udokumentowane ani wspierane przez Filmweb – może zmienić się bez ostrzeżenia.
> Korzystaj z niego zgodnie z regulaminem serwisu i nie zasypuj go zapytaniami (patrz [cache](#cache-i-ponawianie)).

## Spis treści

- [Cechy](#cechy)
- [Instalacja](#instalacja)
- [Użycie](#użycie)
- [API](#api)
- [Obsługa błędów](#obsługa-błędów)
- [Transport HTTP](#transport-http)
- [Własne endpointy](#własne-endpointy)
- [Architektura](#architektura)
- [Wersjonowanie](#wersjonowanie)
- [Development](#development)

## Cechy

- **PHP 8.2+** – klasy `readonly`, enumy, named arguments, generyki w PHPDoc, `strict_types` wszędzie.
- **Zasoby jak w nowoczesnych SDK** – `$filmweb->films`, `$filmweb->people`, `$filmweb->vod`.
- **Typowane modele** – `Film`, `FilmPreview`, `Rating`, `Person`, `VodOffer`… zamiast tablic.
- **Filmy i seriale** – te same endpointy, poprawne linki (`/film/…`, `/serial/…`).
- **Odporność** – dekoratory `RetryingTransport` (429/5xx, exponential backoff) i `CachingTransport` (PSR-16).
- **Zero zależności runtime** poza interfejsami PSR – domyślnie `ext-curl`, opcjonalnie dowolny klient PSR-18.
- **Rozszerzalne** – nowy endpoint to jedna klasa; `raw()` daje surowy dostęp do dowolnej ścieżki.
- **Przetestowane** – PHPUnit na prawdziwych odpowiedziach API, PHPStan `level: max` + strict rules, PER-CS 2.0, CI na PHP 8.2–8.5.

## Instalacja

```bash
composer require nsolutionspl/filmweb-api:^2.0@beta
```

Do czasu wydania `2.0.0` dostępne są wersje beta – po wydaniu wystarczy `composer require nsolutionspl/filmweb-api`.

Wymagania: PHP `^8.2`, `ext-json` oraz `ext-curl` (dla domyślnego transportu).

## Użycie

### Wyszukiwanie

```php
foreach ($filmweb->search('gra o tron') as $hit) {
    echo "#{$hit->id} [{$hit->type}] {$hit->title}\n";   // #476848 [serial] Gra o tron
}
```

### Film lub serial

```php
$film = $filmweb->films->get(500891);   // ?Film – info, preview, oceny i daty (5 zapytań)

$film->info->title;                      // "Incepcja"
$film->info->type;                       // TitleType::Film
$film->info->url();                      // "https://www.filmweb.pl/film/Incepcja-2010-500891"
$film->info->poster?->url();             // "https://fwcdn.pl/fpo/08/91/500891/7354571_1.3.jpg"

$film->preview?->genres;                 // [Genre(10, "Surrealistyczny"), Genre(24, "Thriller"), …]
$film->preview?->countries;              // ["US", "GB"]
$film->preview?->duration;               // 148
$film->preview?->directors;              // [PersonRef(40896, "Christopher Nolan")]
$film->preview?->synopsis;               // krótki opis

$film->rating?->rounded();               // średnia użytkowników
$film->rating?->distribution;            // [1 => …, 10 => …]
$film->criticsRating?->average;          // średnia krytyków
$film->dates?->countryRelease?->date;    // DateTimeImmutable 2010-07-30 (PL)
```

Potrzebujesz tylko części danych? Każdy element ma własną metodę, np. `$filmweb->films->rating(628)`.

### Obsada i osoby

```php
foreach ($filmweb->films->cast(476848, limit: 5) as $member) {
    printf("%s – ocena roli %.1f\n", $member->person?->name, $member->role->rating);
}
// Peter Dinklage – ocena roli 9.6 …

$person = $filmweb->people->get(87);
$person->realName;    // "Keanu Charles Reeves"
$person->birthDate;   // DateTimeImmutable 1964-09-02
$person->knownFor;    // [628, 1012, …] – ID tytułów
```

### Gdzie obejrzeć

```php
foreach ($filmweb->vod->offers(500891) as $offer) {
    echo $offer->provider?->name, ': ', match (true) {
        $offer->subscription => 'w abonamencie',
        $offer->free => 'za darmo',
        default => "wypożyczenie {$offer->rentPrice} zł / zakup {$offer->buyPrice} zł",
    }, "\n";
}
```

Zwracane są tylko oferty aktywne w danej chwili; słownik serwisów pobierany jest raz na instancję.

Więcej w katalogu [`examples/`](examples).

## API

`null` (lub pusta lista) oznacza, że Filmweb zwrócił **404** – to nie jest błąd.

| Wywołanie | Zwraca | Endpoint |
|---|---|---|
| `search(string $query)` | `list<SearchHit>` | `/live/search?query=…` |
| `films->get(int $id)` | `?Film` | info + preview + rating + critics/rating + dates |
| `films->info(int $id)` | `?TitleInfo` | `/title/{id}/info` |
| `films->preview(int $id)` | `?FilmPreview` | `/film/{id}/preview` |
| `films->description(int $id)` | `?string` | `/film/{id}/description` |
| `films->rating(int $id)` | `?Rating` | `/film/{id}/rating` |
| `films->criticsRating(int $id)` | `?Rating` | `/film/{id}/critics/rating` |
| `films->dates(int $id)` | `?ReleaseDates` | `/film/{id}/dates` |
| `films->topRoles(int $id)` | `list<TopRole>` | `/film/{id}/top-roles` |
| `films->cast(int $id, int $limit = 10)` | `list<CastMember>` | top-roles + `/person/{id}/preview` × limit |
| `people->get(int $id)` | `?Person` | `/person/{id}/preview` |
| `vod->providers()` | `array<int, VodProvider>` | `/vod/providers/list` |
| `vod->offers(int $id, ?DateTimeImmutable $at = null)` | `list<VodOffer>` | providers + `/vod/film/{id}/providers/list` |
| `call(Endpoint $endpoint)` | zależnie od endpointu | dowolny |
| `raw(string $path, array $query = [])` | `?Data` | dowolna ścieżka |

## Obsługa błędów

Każdy wyjątek biblioteki implementuje `NSolutions\Filmweb\Exception\FilmwebException`:

| Wyjątek | Kiedy |
|---|---|
| `TransportException` | błąd sieci: DNS, połączenie, TLS, timeout |
| `ApiException` | status HTTP inny niż 2xx/404 (np. `429`, `5xx`) – kod w `$e->status()` |
| `UnexpectedResponseException` | odpowiedź to nie JSON albo brakuje wymaganych pól |
| `InvalidArgumentException` | niepoprawny argument, np. pusta fraza wyszukiwania |

```php
try {
    $film = $filmweb->films->get(628);
} catch (FilmwebException $e) {
    $logger->error('Filmweb unavailable', ['exception' => $e]);
}
```

## Transport HTTP

### Konfiguracja

```php
use NSolutions\Filmweb\Config;
use NSolutions\Filmweb\Http\CurlTransport;

$filmweb = Filmweb::create(
    new CurlTransport(timeout: 10, connectTimeout: 5),
    new Config(locale: 'pl_PL', userAgent: 'MyApp/1.0'),
);
```

### Cache i ponawianie

Transporty to dekoratory – składasz je jak klocki:

```php
use NSolutions\Filmweb\Http\CachingTransport;
use NSolutions\Filmweb\Http\RetryingTransport;

$transport = new CachingTransport(
    new RetryingTransport(new CurlTransport(), maxRetries: 3, baseDelayMs: 500),
    $psr16Cache,          // np. symfony/cache Psr16Cache, Laravel Cache::store()
    ttl: 3600,
);

$filmweb = Filmweb::create($transport);
```

`RetryingTransport` ponawia błędy sieci, `429` i `5xx` (500 ms → 1 s → 2 s…),
`CachingTransport` zapamiętuje odpowiedzi `2xx` i `404`.

### Dowolny klient PSR-18

```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use NSolutions\Filmweb\Http\Psr18Transport;

$filmweb = Filmweb::create(new Psr18Transport(new Client(), new HttpFactory()));
```

## Własne endpointy

API ma więcej ścieżek niż te opakowane w bibliotece (np. `/film/{id}/votes/popular`, `/users/{name}/id`).
Najpierw podejrzyj odpowiedź:

```php
print_r($filmweb->raw('/users/Shadow_filmweb/id')?->toArray());
// ['name' => 'Shadow_filmweb', 'userId' => 1681862]
```

…a potem dodaj typowany endpoint – bez modyfikowania biblioteki:

```php
use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Support\Data;

/** @implements Endpoint<int> */
final readonly class GetUserId implements Endpoint
{
    public function __construct(private string $username) {}

    public function path(): string
    {
        return '/users/' . rawurlencode($this->username) . '/id';
    }

    public function query(): array
    {
        return [];
    }

    public function map(Data $data): int
    {
        return $data->int('userId');
    }
}

$filmweb->call(new GetUserId('Shadow_filmweb')); // 1681862
```

Dla ścieżek `/film/{id}/…` wystarczy rozszerzyć `FilmEndpoint` i podać `resource()`.
`Data` daje typowany dostęp do JSON-a, także po ścieżkach: `$data->nullableString('plot.synopsis')`.

## Architektura

```
src/
├── Filmweb.php                 # punkt wejścia: search(), call(), raw() + zasoby
├── Config.php                  # niemutowalna konfiguracja (base URL, locale, User-Agent)
├── Resource/                   # FilmResource, PersonResource, VodResource – API dla użytkownika
├── Api/
│   ├── ApiClient.php           # URL → GET → JSON → Endpoint::map(), 404 → null
│   ├── Endpoint.php            # kontrakt endpointu (generyczny: Endpoint<TResult>)
│   ├── Endpoint/               # Film/, Person/, Title/, Vod/, Search/ – po klasie na endpoint
│   └── Mapping/                # wspólne mapowania
├── Http/                       # Transport + Curl/Psr18 + dekoratory Retrying/Caching
├── Model/                      # readonly DTO, value object Image, enumy
├── Support/                    # Data (typowany JSON), Markup
└── Exception/                  # FilmwebException + implementacje
```

- **Zasoby** grupują operacje domenowo i ukrywają liczbę zapytań (np. `films->get()` = 5 endpointów).
- **Endpointy** są małe i niezależne: opisują ścieżkę i mapują odpowiedź – nic więcej (SRP, OCP).
- **Transport** to jedna metoda `get()`; retry i cache są dekoratorami, nie flagami w kliencie.
- Testy podmieniają tylko transport (`tests/Fixtures/FakeTransport.php`) i używają prawdziwych odpowiedzi API
  z `tests/Fixtures/responses/`.

## Wersjonowanie

Projekt stosuje [Semantic Versioning](https://semver.org/lang/pl/), zmiany opisuje [CHANGELOG.md](CHANGELOG.md).

**Publiczne API** (objęte obietnicą zgodności): `Filmweb`, `Config`, zasoby z `Resource/` (bez konstruktorów),
`Model/`, `Exception/`, `Http/` (interfejs `Transport` i jego implementacje), `Api/Endpoint`, `Api/Endpoint/**`
oraz `Support/Data`. Klasy i metody oznaczone `@internal` mogą zmienić się w dowolnej wersji.

| Wersja | Co może się zmienić |
|---|---|
| **PATCH** `2.0.x` | poprawki, np. dostosowanie mapowania do zmian w odpowiedziach Filmweb |
| **MINOR** `2.x.0` | nowe endpointy, metody i **opcjonalne** pola modeli (na końcu konstruktora, z wartością domyślną) |
| **MAJOR** `3.0.0` | usunięcie/zmiana publicznego API, podniesienie minimalnego PHP; wcześniej `@deprecated` w wersji minor |

| Gałąź | Wersje | Status |
|---|---|---|
| `master` | 2.x | rozwijana |
| [`1.x`](https://github.com/b44x/filmweb-api/tree/1.x) | 1.x | legacy – nie działa (mobilne API wyłączone), bez wsparcia |

Wydania tworzy workflow [Release](.github/workflows/release.yml) – tag, GitHub Release i notatki z CHANGELOG.

## Development

```bash
composer install
composer test      # PHPUnit
composer analyse   # PHPStan (level max)
composer cs        # PHP-CS-Fixer (dry-run), `composer cs:fix` naprawia
composer check     # wszystko naraz
```

## Licencja

[MIT](LICENSE). Dane pochodzą z serwisu Filmweb.pl i należą do ich właścicieli.

Projekt jest kontynuacją [filmweb-php](https://github.com/nSolutionsPL/filmweb-php).
