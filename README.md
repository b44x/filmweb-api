# Filmweb API for PHP

[![CI](https://github.com/b44x/filmweb-api/actions/workflows/ci.yml/badge.svg)](https://github.com/b44x/filmweb-api/actions/workflows/ci.yml)
![PHP](https://img.shields.io/badge/php-%5E8.2-777BB4?logo=php&logoColor=white)
![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen)
![Code style](https://img.shields.io/badge/code%20style-PER--CS%202.0-blue)

Nieoficjalny, w pełni typowany klient REST API [Filmweb.pl](https://www.filmweb.pl) (`https://www.filmweb.pl/api/v1`) –
tego samego, z którego korzysta strona filmweb.pl.

```php
$filmweb = Filmweb::create();

$hit  = $filmweb->search('matrix')[0];   // SearchHit { id: 628, title: "Matrix", type: "film" }
$film = $filmweb->film($hit->id);

echo "{$film->info->title} ({$film->info->year}) – ★ {$film->rating?->rounded()}";
// Matrix (1999) – ★ 7.6
```

> [!NOTE]
> To jest **2.x** – przepisana od zera wersja dla PHP 8.2+, oparta na nowym REST API.
> Stare mobilne API (`ssl.filmweb.pl/api`), z którego korzystała wersja 1.x, **już nie działa**.
> Wersja legacy (PHP 5.4) jest dostępna pod tagiem [`v1.0.0`](https://github.com/b44x/filmweb-api/tree/v1.0.0). Przechodzisz z 1.x? Zobacz [UPGRADE.md](UPGRADE.md).

> [!WARNING]
> API nie jest oficjalnie udokumentowane ani wspierane przez Filmweb – może zmienić się bez ostrzeżenia.
> Korzystaj z niego zgodnie z regulaminem serwisu i nie zasypuj go zapytaniami.

## Spis treści

- [Cechy](#cechy)
- [Instalacja](#instalacja)
- [Użycie](#użycie)
- [Dostępne metody](#dostępne-metody)
- [Obsługa błędów](#obsługa-błędów)
- [Konfiguracja i transport HTTP](#konfiguracja-i-transport-http)
- [Własne endpointy](#własne-endpointy)
- [Architektura](#architektura)
- [Development](#development)

## Cechy

- **PHP 8.2+** – klasy `readonly`, enumy, named arguments, `strict_types` wszędzie.
- **Typowane modele zamiast tablic** – `Film`, `Preview`, `Rating`, `Person`, `VodOffer`… z podpowiadaniem w IDE.
- **Zero zależności runtime** poza interfejsami PSR – domyślnie działa na `ext-curl`.
- **PSR-18 ready** – podepnij Guzzle, Symfony HttpClient albo dowolny inny klient.
- **Rozszerzalne** – nowy endpoint to jedna klasa; do tego surowy dostęp `raw()` do dowolnej ścieżki API.
- **Przetestowane** – PHPUnit na prawdziwych odpowiedziach API, PHPStan `level: max` + strict rules, PER-CS 2.0, CI na PHP 8.2–8.5.

## Instalacja

```bash
composer require nsolutionspl/filmweb-api
```

Wymagania: PHP `^8.2`, `ext-json` oraz `ext-curl` (dla domyślnego transportu).

## Użycie

### Wyszukiwanie

```php
foreach ($filmweb->search('matrix') as $hit) {
    echo "#{$hit->id} [{$hit->type}] {$hit->title}\n";   // #628 [film] Matrix
}

$hit->titleType();  // TitleType::Film | Serial | Game | null (np. dla osób)
$hit->mainCast;     // list<PersonRef>
```

### Wszystko o filmie naraz

`film()` łączy pięć endpointów (info, preview, oceny użytkowników i krytyków, daty premier):

```php
$film = $filmweb->film(500891); // ?Film – null, gdy tytuł nie istnieje

$film->info->title;                          // "Incepcja"
$film->info->type;                           // TitleType::Film
$film->preview?->originalTitle;              // "Inception"
$film->preview?->duration;                   // 148 (minuty)
$film->preview?->genres;                     // [Genre(10, "Surrealistyczny"), Genre(24, "Thriller"), …]
$film->preview?->countries;                  // ["US", "GB"]
$film->preview?->directors;                  // [PersonRef(40896, "Christopher Nolan")]
$film->preview?->mainCast;                   // [PersonRef(30, "Leonardo DiCaprio"), …]
$film->preview?->posterUrl;                  // "https://fwcdn.pl/fpo/08/91/500891/7354571_1.6.jpg"
$film->rating?->rounded();                   // średnia użytkowników, np. 7.6
$film->rating?->distribution;                // [1 => …, 10 => …]
$film->criticsRating?->average;              // średnia krytyków
$film->dates?->worldPremiere?->date;         // DateTimeImmutable 2010-07-08 (GB)
$film->dates?->countryRelease?->date;        // DateTimeImmutable 2010-07-30 (PL)
```

### Obsada i osoby

```php
foreach ($filmweb->topCast(500891, limit: 5) as $member) {
    printf("%s – ocena roli %.1f\n", $member->person?->name, $member->role->rating);
}

$person = $filmweb->person(87);
$person->realName;   // "Keanu Charles Reeves"
$person->birthDate;  // DateTimeImmutable 1964-09-02
$person->knownFor;   // [628, 1012, …] – ID tytułów
```

`topCast()` wykonuje 1 + `$limit` zapytań (lista ról zawiera tylko ID osób) – `topRoles()` zwraca same role.

### Gdzie obejrzeć (VOD)

```php
foreach ($filmweb->whereToWatch(500891) as $offer) {
    echo $offer->provider?->name, ': ', match (true) {
        $offer->subscription => 'w abonamencie',
        $offer->free => 'za darmo',
        default => sprintf('wypożyczenie %.2f zł / zakup %.2f zł', $offer->rentPrice, $offer->buyPrice),
    }, " – {$offer->url}\n";
}
```

Zwracane są tylko oferty aktywne w danej chwili (drugi argument pozwala podać inną datę).

### Pojedyncze endpointy

```php
$filmweb->info(628);           // ?TitleInfo – działa też dla seriali i gier
$filmweb->preview(628);        // ?Preview
$filmweb->description(628);    // ?string – pełny opis, bez znaczników [person=…]
$filmweb->rating(628);         // ?Rating
$filmweb->criticsRating(628);  // ?Rating
$filmweb->dates(628);          // ?ReleaseDates
$filmweb->topRoles(628);       // list<TopRole>
$filmweb->vodProviders();      // array<int, VodProvider>
```

Więcej w katalogu [`examples/`](examples).

## Dostępne metody

| Metoda | Zwraca | Endpoint |
|---|---|---|
| `search(string $query)` | `list<SearchHit>` | `GET /live/search?query=…` |
| `film(int $id)` | `?Film` | info + preview + rating + critics/rating + dates |
| `info(int $id)` | `?TitleInfo` | `GET /title/{id}/info` |
| `preview(int $id)` | `?Preview` | `GET /film/{id}/preview` |
| `description(int $id)` | `?string` | `GET /film/{id}/description` |
| `rating(int $id)` | `?Rating` | `GET /film/{id}/rating` |
| `criticsRating(int $id)` | `?Rating` | `GET /film/{id}/critics/rating` |
| `dates(int $id)` | `?ReleaseDates` | `GET /film/{id}/dates` |
| `topRoles(int $id)` | `list<TopRole>` | `GET /film/{id}/top-roles` |
| `topCast(int $id, int $limit = 10)` | `list<CastMember>` | top-roles + `GET /person/{id}/preview` × limit |
| `person(int $id)` | `?Person` | `GET /person/{id}/preview` |
| `vodProviders()` | `array<int, VodProvider>` | `GET /vod/providers/list` |
| `whereToWatch(int $id, ?DateTimeImmutable $at = null)` | `list<VodOffer>` | providers + `GET /vod/film/{id}/providers/list` |
| `call(Endpoint $endpoint)` | zależnie od endpointu | dowolny |
| `raw(string $path, array $query = [])` | `?Data` | dowolna ścieżka |

`null` oznacza, że Filmweb zwrócił **404** – to nie jest błąd.

## Obsługa błędów

Każdy wyjątek rzucany przez bibliotekę implementuje `NSolutions\Filmweb\Exception\FilmwebException`:

| Wyjątek | Kiedy |
|---|---|
| `TransportException` | błąd sieci: DNS, połączenie, TLS, timeout |
| `ApiException` | status HTTP inny niż 2xx/404 (np. `429`, `5xx`) – kod w `$e->status()` |
| `UnexpectedResponseException` | odpowiedź to nie JSON albo brakuje wymaganych pól |

```php
use NSolutions\Filmweb\Exception\FilmwebException;

try {
    $film = $filmweb->film(628);
} catch (FilmwebException $e) {
    $logger->error('Filmweb unavailable', ['exception' => $e]);
}
```

## Konfiguracja i transport HTTP

```php
use NSolutions\Filmweb\Config;
use NSolutions\Filmweb\Http\CurlTransport;

$filmweb = Filmweb::create(
    new CurlTransport(timeout: 10, connectTimeout: 5),
    new Config(
        locale: 'pl_PL',              // nagłówek x-locale
        userAgent: 'MyApp/1.0',
        cdnUrl: 'https://fwcdn.pl',
    ),
);
```

### Dowolny klient PSR-18

```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use NSolutions\Filmweb\Http\Psr18Transport;

$filmweb = Filmweb::create(new Psr18Transport(new Client(), new HttpFactory()));
```

Potrzebujesz cache, retry albo rate-limitingu? Zaimplementuj `Transport` jako dekorator – to jedna metoda `get()`.

## Własne endpointy

API ma więcej endpointów niż te opakowane w bibliotece. Najpierw podejrzyj odpowiedź:

```php
print_r($filmweb->raw('/film/628/info')?->toArray());
```

…a potem dodaj typowany endpoint bez modyfikowania biblioteki (Open/Closed):

```php
use NSolutions\Filmweb\Api\Endpoint\FilmEndpoint;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;

/** @extends FilmEndpoint<string> */
final readonly class GetFilmSubType extends FilmEndpoint
{
    protected function resource(): string
    {
        return 'info';                 // GET /film/{id}/info
    }

    public function map(Data $data, ImageUrls $images): string
    {
        return $data->string('subType');
    }
}

$filmweb->call(new GetFilmSubType(628)); // "film_cinema"
```

`Data` daje typowany dostęp do JSON-a, także po ścieżkach: `$data->nullableString('plot.synopsis')`.

## Architektura

```
src/
├── Filmweb.php               # fasada – publiczne, typowane API
├── Config.php                # niemutowalna konfiguracja
├── Api/
│   ├── ApiClient.php         # URL → GET → JSON → mapowanie, 404 → null
│   ├── Endpoint.php          # kontrakt endpointu (generyczny, @template)
│   └── Endpoint/             # po jednej klasie na endpoint + FilmEndpoint (baza /film/{id}/…)
├── Http/                     # Transport, Response, CurlTransport, Psr18Transport
├── Model/                    # readonly DTO (Film, Preview, Person, VodOffer…) + enum TitleType
├── Support/                  # Data (typowany JSON), ImageUrls, Markup
└── Exception/                # FilmwebException + implementacje
```

Każda warstwa ma jedną odpowiedzialność i zależy od abstrakcji (`Transport`, `Endpoint`),
więc w testach podmieniasz tylko transport – zobacz `tests/Fixtures/FakeTransport.php`
i prawdziwe odpowiedzi API w `tests/Fixtures/responses/`.

## Development

```bash
composer install
composer test      # PHPUnit
composer analyse   # PHPStan (level max)
composer cs        # PHP-CS-Fixer (dry-run), `composer cs:fix` naprawia
composer check     # wszystko naraz
```

## Licencja

Kod: [LICENSE.md](LICENSE.md). Dane pochodzą z serwisu Filmweb.pl i należą do ich właścicieli.

Projekt jest kontynuacją [filmweb-php](https://github.com/nSolutionsPL/filmweb-php).
