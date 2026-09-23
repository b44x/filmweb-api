# Aktualizacja z 1.x do 2.0

Wersja 1.x korzystała z mobilnego API (`ssl.filmweb.pl/api`), które **przestało działać**.
Wersja 2.0 używa REST API strony (`www.filmweb.pl/api/v1`), więc zmieniły się zarówno kod, jak i dostępne dane.

## Instalacja i autoloading

```diff
- require_once 'Filmweb.php';
- $filmweb = \nSolutions\Filmweb::instance();
+ require 'vendor/autoload.php';
+ $filmweb = \NSolutions\Filmweb\Filmweb::create();
```

## Metody

| 1.x | 2.0 |
|---|---|
| `getFilmInfoFull($id)->execute()` | `film($id)` (zbiorczo) albo `info($id)`, `preview($id)`, `rating($id)`, `dates($id)` |
| `getFilmDescription($id)->execute()` | `description($id)` |
| `getFilmPersons($id, $type, $page)` | `topCast($id)`, `preview($id)->directors`, `person($id)` |
| `Login`, `getUserFilmVotes`, `getFilmComments`, `getFilmImages`, `getFilmVideos`, `getFilmReview` | brak – patrz niżej |
| — | `search($query)`, `whereToWatch($id)` (nowość) |

Brakujące metody możesz dodać samodzielnie: podejrzyj endpoint przez `$filmweb->raw('/ścieżka')`,
a potem zaimplementuj `Endpoint` (albo `FilmEndpoint`) – szczegóły w README, sekcja „Własne endpointy”.

## Zmiany w danych

- Zamiast `stdClass`/`false` dostajesz typowane obiekty albo `null` (HTTP 404).
- `avgRate`/`votesCount` → `Rating::$average`/`Rating::$count` (+ `distribution`, `wantToSeeCount`).
- `imagePath` → `TitleInfo::$posterUrl` (CDN `fwcdn.pl`).
- `filmType` → enum `TitleType` (`film`, `serial`, `game`).
- Opisy nie zawierają znaczników `[person=…]` – są czystym tekstem.

## Błędy

Zamiast ogólnego `\Exception` łap `NSolutions\Filmweb\Exception\FilmwebException`
(lub konkretne: `TransportException`, `ApiException`, `UnexpectedResponseException`).
