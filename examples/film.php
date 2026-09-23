<?php

declare(strict_types=1);

/**
 * Usage: php examples/film.php "incepcja"
 */

use NSolutions\Filmweb\Exception\FilmwebException;
use NSolutions\Filmweb\Filmweb;
use NSolutions\Filmweb\Model\Genre;
use NSolutions\Filmweb\Model\PersonRef;

require __DIR__ . '/../vendor/autoload.php';

$query = $argv[1] ?? 'incepcja';
$filmweb = Filmweb::create();
$names = static fn(array $items): string => implode(', ', array_map(static fn(Genre|PersonRef $item): string => $item->name, $items));

try {
    $hit = $filmweb->search($query)[0] ?? exit("Nic nie znaleziono dla \"{$query}\".\n");
    $film = $filmweb->film($hit->id) ?? exit("Tytuł #{$hit->id} nie istnieje.\n");
    $preview = $film->preview;

    printf("%s (%d)%s\n", $film->info->title, $film->info->year ?? 0, $film->info->originalTitle !== null ? " – {$film->info->originalTitle}" : '');
    printf("%s | %d min | %s\n", $names($preview->genres ?? []), $preview->duration ?? 0, implode(', ', $preview->countries ?? []));
    printf("Ocena: %.1f/10 (%s głosów), krytycy: %.1f/10\n", $film->rating?->average ?? 0, number_format($film->rating->count ?? 0, 0, ',', ' '), $film->criticsRating?->average ?? 0);
    printf("Premiera w Polsce: %s\n", $film->dates?->countryRelease?->date->format('d.m.Y') ?? '—');
    printf("Reżyseria: %s\nObsada: %s\n\n%s\n\n", $names($preview->directors ?? []), $names($preview->mainCast ?? []), $preview?->synopsis);

    foreach ($filmweb->whereToWatch($hit->id) as $offer) {
        printf("▶ %s: %s\n", $offer->provider->name ?? "#{$offer->providerId}", match (true) {
            $offer->subscription => 'w abonamencie',
            $offer->free => 'za darmo',
            default => trim(sprintf('%s %s', $offer->rentPrice !== null ? "wypożyczenie {$offer->rentPrice} zł" : '', $offer->buyPrice !== null ? "zakup {$offer->buyPrice} zł" : '')),
        });
    }
} catch (FilmwebException $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
