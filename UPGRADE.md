# Aktualizacja z 1.x do 2.0

> Nie możesz teraz migrować? Przypnij starą wersję: `composer require nsolutionspl/filmweb-api:^1.0`
> (pamiętaj, że 1.x nie działa – Filmweb wyłączył mobilne API).

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
| `getFilmInfoFull($id)->execute()` | `films->get($id)` (zbiorczo) albo `films->info()`, `->preview()`, `->rating()`, `->dates()` |
| `getFilmDescription($id)->execute()` | `films->description($id)` |
| `getFilmPersons($id, $type, $page)` | `films->cast($id)`, `films->preview($id)->directors`, `people->get($id)` |
| `Login`, `getUserFilmVotes`, `getFilmComments`, `getFilmImages`, `getFilmVideos`, `getFilmReview` | brak – patrz niżej |
| — | `search($query)`, `vod->offers($id)` (nowość) |

Brakujące metody możesz dodać samodzielnie: podejrzyj endpoint przez `$filmweb->raw('/ścieżka')`,
a potem zaimplementuj `Endpoint` (albo `FilmEndpoint`) – szczegóły w README, sekcja „Własne endpointy”.

## Zmiany w danych

- Zamiast `stdClass`/`false` dostajesz typowane obiekty albo `null` (HTTP 404).
- `avgRate`/`votesCount` → `Rating::$average`/`Rating::$count` (+ `distribution`, `wantToSeeCount`).
- `imagePath` → `TitleInfo::$poster` (`Image`, `->url($size)`; CDN `fwcdn.pl`).
- `filmType` → enum `TitleType` (`film`, `serial`, `game`).
- Opisy nie zawierają znaczników `[person=…]` – są czystym tekstem.

## Błędy

Zamiast ogólnego `\Exception` łap `NSolutions\Filmweb\Exception\FilmwebException`
(lub konkretne: `TransportException`, `ApiException`, `UnexpectedResponseException`).
