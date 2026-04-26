<?php

namespace App\Service;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SteamGameService
{
    private string $steamApiKey;

    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $em,
    ) {
        $this->steamApiKey = $_ENV['STEAM_API_KEY'] ?? '';
    }

    public function searchGames(string $query): array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://store.steampowered.com/api/storesearch/', [
                'query' => [
                    'term' => $query,
                    'l' => 'english',
                    'cc' => 'US',
                ],
            ]);

            $data = $response->toArray(false);
            $results = [];

            foreach ($data['items'] ?? [] as $item) {
                $results[] = [
                    'appId' => $item['id'],
                    'name' => $item['name'],
                    'image' => $item['tiny_image'] ?? null,
                ];
            }

            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getGameDetails(int $appId): ?array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://store.steampowered.com/api/appdetails', [
                'query' => [
                    'appids' => $appId,
                    'l' => 'english',
                ],
            ]);

            $data = $response->toArray(false);
            $gameData = $data[$appId]['data'] ?? null;

            if (!$gameData || ($data[$appId]['success'] ?? false) === false) {
                return null;
            }

            $genres = array_map(fn($g) => $g['description'], $gameData['genres'] ?? []);

            return [
                'appId' => $appId,
                'name' => $gameData['name'],
                'description' => strip_tags($gameData['short_description'] ?? ''),
                'image' => $gameData['header_image'] ?? null,
                'genre' => $genres[0] ?? 'Other',
                'genres' => $genres,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function addGameFromSteam(int $appId): ?Game
    {
        $gameRepo = $this->em->getRepository(Game::class);

        $existing = $gameRepo->findOneBy(['steamAppId' => $appId]);
        if ($existing) {
            return $existing;
        }

        $details = $this->getGameDetails($appId);
        if (!$details) {
            return null;
        }

        $game = new Game();
        $game->setName($details['name']);
        $game->setSteamAppId($appId);
        $game->setDescription($details['description']);
        $game->setImageUrl($details['image']);
        $game->setGenre($this->mapGenre($details['genre']));
        $game->setSlug(strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($details['name']))));

        $this->em->persist($game);
        $this->em->flush();

        return $game;
    }

    public function importTopGames(): array
    {
        // Popular Steam App IDs
        $topAppIds = [
            730, 570, 578080, 1172470, 252490, 892970, 105600, 242760,
            440, 359550, 594650, 289070, 1466860, 394360, 1966720,
            739630, 548430, 550, 1971870, 1364780, 252950, 2399830,
            271590, // GTA V
            1091500, // Cyberpunk
            413150, // Stardew Valley
            1245620, // Elden Ring
            1174180, // Red Dead Redemption 2
            814380, // Sekiro
            292030, // The Witcher 3
            1086940, // Baldur's Gate 3
            367520, // Hollow Knight
            1151640, // Horizon Zero Dawn
            1238810, // Battlefield 2042
            1517290, // Battlefield 2042
            230410, // Warframe
            238960, // Path of Exile
            218620, // Payday 2
            582010, // Monster Hunter World
            1222670, // The Sims 4
            346110, // ARK Survival Evolved
        ];

        $imported = [];
        $gameRepo = $this->em->getRepository(Game::class);

        foreach ($topAppIds as $appId) {
            $existing = $gameRepo->findOneBy(['steamAppId' => $appId]);
            if ($existing) {
                continue;
            }

            $game = $this->addGameFromSteam($appId);
            if ($game) {
                $imported[] = $game->getName();
            }

            usleep(300000);
        }

        return $imported;
    }

    private function mapGenre(string $steamGenre): string
    {
        $map = [
            'Action' => 'FPS',
            'Free to Play' => 'FPS',
            'Adventure' => 'RPG',
            'Strategy' => 'Strategy',
            'RPG' => 'RPG',
            'Simulation' => 'Simulation',
            'Sports' => 'Sports',
            'Racing' => 'Racing',
            'Massively Multiplayer' => 'MMO',
            'Indie' => 'Other',
            'Casual' => 'Other',
        ];

        return $map[$steamGenre] ?? 'Other';
    }

    /**
     * Search for a game image by name using RAWG API
     */
    public function findImageByName(string $gameName): ?string
    {
        try {
            // RAWG API - free gaming database
            $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games', [
                'query' => [
                    'search' => $gameName,
                    'page_size' => 1,
                    'key' => 'c542e67aec3a4340908f9de9e86038af',
                ],
            ]);

            $data = $response->toArray(false);
            $results = $data['results'] ?? [];

            if (!empty($results) && !empty($results[0]['background_image'])) {
                return $results[0]['background_image'];
            }
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Find image for a game: try Steam first, then RAWG, then null
     */
    public function findImage(Game $game): ?string
    {
        if ($game->getSteamAppId()) {
            $url = 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/' . $game->getSteamAppId() . '/header.jpg';
            return $url;
        }

        $image = $this->findImageByName($game->getName());
        if ($image) {
            return $image;
        }

        return null;
    }
}
