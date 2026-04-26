<?php

namespace App\Service;

use App\Entity\Achievement;
use App\Entity\Game;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SteamAchievementService
{
    private string $steamApiKey;

    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $em,
    ) {
        $this->steamApiKey = $_ENV['STEAM_API_KEY'] ?? '';
    }

    public function resolveToSteamId64(string $input): ?string
    {
        $input = trim($input);

        // Already a SteamID64
        if (preg_match('/^[0-9]{17}$/', $input)) {
            return $input;
        }

        // URL: https://steamcommunity.com/profiles/76561198XXXXXXXXX
        if (preg_match('#steamcommunity\.com/profiles/([0-9]{17})#', $input, $m)) {
            return $m[1];
        }

        // URL: https://steamcommunity.com/id/custom_name
        if (preg_match('#steamcommunity\.com/id/([^/]+)#', $input, $m)) {
            return $this->resolveVanityUrl($m[1]);
        }

        // Just a vanity name
        if (preg_match('/^[a-zA-Z0-9_-]+$/', $input)) {
            return $this->resolveVanityUrl($input);
        }

        return null;
    }

    private function getAchievementSchema(int $appId): array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/', [
                'query' => [
                    'appid' => $appId,
                    'key' => $this->steamApiKey,
                    'l' => 'ukrainian',
                ],
            ]);

            $data = $response->toArray(false);
            $achievements = $data['game']['availableGameStats']['achievements'] ?? [];

            $schema = [];
            foreach ($achievements as $ach) {
                $schema[$ach['name']] = [
                    'displayName' => $ach['displayName'] ?? $ach['name'],
                    'description' => $ach['description'] ?? null,
                    'icon' => $ach['icon'] ?? null,
                    'iconGray' => $ach['icongray'] ?? null,
                ];
            }

            return $schema;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function resolveVanityUrl(string $vanityName): ?string
    {
        if (empty($this->steamApiKey)) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/', [
                'query' => [
                    'key' => $this->steamApiKey,
                    'vanityurl' => $vanityName,
                ],
            ]);

            $data = $response->toArray(false);
            if (($data['response']['success'] ?? 0) === 1) {
                return $data['response']['steamid'];
            }
        } catch (\Exception $e) {}

        return null;
    }

    public function syncAchievements(User $user, Game $game): array|string
    {
        if (!$user->getSteamId()) {
            return 'Вкажіть Steam ID (64-bit) у налаштуваннях профілю. Знайдіть його на steamid.io';
        }
        if (!$game->getSteamAppId()) {
            return 'Ця гра не має Steam App ID';
        }
        if (empty($this->steamApiKey)) {
            return 'Steam API ключ не налаштовано';
        }

        try {
            // 1. Get achievement schema (names, descriptions, icons)
            $schema = $this->getAchievementSchema($game->getSteamAppId());

            // 2. Get player achievements
            $response = $this->httpClient->request('GET', 'https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/', [
                'query' => [
                    'appid' => $game->getSteamAppId(),
                    'key' => $this->steamApiKey,
                    'steamid' => $user->getSteamId(),
                ],
            ]);

            $data = $response->toArray(false);

            if (!isset($data['playerstats']['achievements'])) {
                $errorMsg = $data['playerstats']['error'] ?? 'Не вдалося отримати досягнення';
                return 'Steam API: ' . $errorMsg;
            }

            $achievements = $data['playerstats']['achievements'];
            $synced = [];

            foreach ($achievements as $achData) {
                if ($achData['achieved'] ?? false) {
                    $apiName = $achData['apiname'];
                    $existing = $this->em->getRepository(Achievement::class)->findOneBy([
                        'user' => $user,
                        'game' => $game,
                        'steamAchievementId' => $apiName,
                    ]);

                    if (!$existing) {
                        $schemaInfo = $schema[$apiName] ?? null;

                        $achievement = new Achievement();
                        $achievement->setUser($user);
                        $achievement->setGame($game);
                        $achievement->setName($schemaInfo['displayName'] ?? $apiName);
                        $achievement->setDescription($schemaInfo['description'] ?? null);
                        $achievement->setIconUrl($schemaInfo['icon'] ?? null);
                        $achievement->setSteamAchievementId($apiName);
                        $achievement->setUnlockedAt(
                            isset($achData['unlocktime']) ? (new \DateTime())->setTimestamp($achData['unlocktime']) : new \DateTime()
                        );
                        $this->em->persist($achievement);
                        $synced[] = $achievement;
                    } elseif ($existing && $schema) {
                        // Update existing achievements with missing data
                        $schemaInfo = $schema[$apiName] ?? null;
                        $updated = false;
                        if ($schemaInfo && !$existing->getDescription() && !empty($schemaInfo['description'])) {
                            $existing->setDescription($schemaInfo['description']);
                            $updated = true;
                        }
                        if ($schemaInfo && !$existing->getIconUrl() && !empty($schemaInfo['icon'])) {
                            $existing->setIconUrl($schemaInfo['icon']);
                            $updated = true;
                        }
                        if ($schemaInfo && $existing->getName() === $apiName && !empty($schemaInfo['displayName'])) {
                            $existing->setName($schemaInfo['displayName']);
                            $updated = true;
                        }
                    }
                }
            }

            $this->em->flush();
            return $synced;
        } catch (\Exception $e) {
            return 'Помилка: ' . $e->getMessage();
        }
    }
}
