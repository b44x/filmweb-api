<?php

declare(strict_types=1);

/**
 * Usage: php examples/film.php "gra o tron"
 */

use NSolutions\Filmweb\Exception\FilmwebException;
use NSolutions\Filmweb\Filmweb;
use NSolutions\Filmweb\Model\Genre;
use NSolutions\Filmweb\Model\PersonRef;
use NSolutions\Filmweb\Model\VodOffer;

require __DIR__ . '/../vendor/autoload.php';

$query = $argv[1] ?? 'incepcja';
$filmweb = Filmweb::create();

/** @param list<Genre|PersonRef> $items */
$names = static fn(array $items): string => implode(', ', array_map(static fn(Genre|PersonRef $item): string => $item->name, $items));

$price = static fn(VodOffer $offer): string => match (true) {
    $offer->subscription => 'w abonamencie',
    $offer->free => 'za darmo',
    default => implode(', ', array_filter([
        $offer->rentPrice === null ? null : sprintf('wypożyczenie %.2f zł', $offer->rentPrice),
        $offer->buyPrice === null ? null : sprintf('zakup %.2f zł', $offer->buyPrice),
    ])),
};

try {
    $hit = $filmweb->search($query)[0] ?? exit("Nic nie znaleziono dla \"{$query}\".\n");
    $film = $filmweb->films->get($hit->id) ?? exit("Tytuł #{$hit->id} nie istnieje.\n");
    $preview = $film->preview;

    printf("%s (%d) – %s\n", $film->info->title, $film->info->year ?? 0, $film->info->url());
    printf("%s | %d min | %s\n", $names($preview->genres ?? []), $preview->duration ?? 0, implode(', ', $preview->countries ?? []));
    printf("Ocena: %.1f/10 (%s głosów), krytycy: %.1f/10\n", $film->rating?->average ?? 0, number_format($film->rating->count ?? 0, 0, ',', ' '), $film->criticsRating?->average ?? 0);
    printf("Premiera w Polsce: %s\n", $film->dates?->countryRelease?->date->format('d.m.Y') ?? '—');
    printf("Reżyseria: %s\n\n%s\n\nNajlepiej oceniane role:\n", $names($preview->directors ?? []), $preview?->synopsis);

    foreach ($filmweb->films->cast($hit->id, limit: 5) as $member) {
        printf("  %-25s %.1f\n", $member->person->name ?? "#{$member->role->personId}", $member->role->rating);
    }

    foreach ($filmweb->vod->offers($hit->id) as $offer) {
        printf("▶ %s: %s\n", $offer->provider->name ?? "#{$offer->providerId}", $price($offer));
    }
} catch (FilmwebException $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
