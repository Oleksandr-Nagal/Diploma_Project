<?php

namespace App\DataFixtures;

use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GameFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $gameRepo = $manager->getRepository(Game::class);

        $games = [
            ['name' => 'Counter-Strike 2', 'genre' => 'FPS', 'maxPlayers' => 5, 'steamAppId' => 730,
                'description' => 'Легендарний тактичний шутер 5v5 від Valve. Змагайтесь у ранкових матчах, вдосконалюйте стрільбу та командну тактику.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/730/header.jpg'],
            ['name' => 'Valorant', 'genre' => 'FPS', 'maxPlayers' => 5, 'steamAppId' => null,
                'description' => 'Тактичний шутер 5v5 від Riot Games з унікальними агентами та здібностями. Точна стрільба і командна стратегія.',
                'image' => 'https://cdn1.epicgames.com/offer/cbd5b3d310a54b12bf3fe8c41994174f/EGS_VALORANT_RiotGames_S1_2560x1440-b88adde6a97e0a58ccf17b80ffe01542'],
            ['name' => 'Overwatch 2', 'genre' => 'FPS', 'maxPlayers' => 5, 'steamAppId' => null,
                'description' => 'Командний шутер з героями від Blizzard. 5v5 бої з унікальними персонажами та їхніми здібностями.',
                'image' => 'https://blz-contentstack-images.akamaized.net/v3/assets/blt9c12f249ac15c7ec/bltbcf2689c29fa39eb/622906a991f4232f0085d3cc/Masthead_Overwatch2_702x400.jpg'],
            ['name' => 'Rainbow Six Siege', 'genre' => 'FPS', 'maxPlayers' => 5, 'steamAppId' => 359550,
                'description' => 'Тактичний шутер від Ubisoft з руйнуванням оточення. Оператори з унікальними гаджетами, напруга кожного раунду.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/359550/header.jpg'],
            ['name' => 'Call of Duty: Warzone', 'genre' => 'FPS', 'maxPlayers' => 4, 'steamAppId' => null,
                'description' => 'Безкоштовний Battle Royale від Activision на 150 гравців. Динамічні бої та постійні оновлення.',
                'image' => 'https://www.callofduty.com/content/dam/atvi/callofduty/cod-touchui/blog/hero/mw-wz/WZ-Season-Three-background.jpg'],
            ['name' => 'Escape from Tarkov', 'genre' => 'FPS', 'maxPlayers' => 5, 'steamAppId' => null,
                'description' => 'Хардкорний шутер-виживання з реалістичною балістикою. Рейди, лут, прокачка персонажа.',
                'image' => 'https://www.escapefromtarkov.com/uploads/media/default/0001/01/thumb_326_default_header.jpeg'],
            ['name' => 'Team Fortress 2', 'genre' => 'FPS', 'maxPlayers' => 12, 'steamAppId' => 440,
                'description' => 'Класичний безкоштовний командний шутер від Valve з 9 унікальними класами та мультяшною графікою.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/440/header.jpg'],
            ['name' => 'Hunt: Showdown', 'genre' => 'FPS', 'maxPlayers' => 3, 'steamAppId' => 594650,
                'description' => 'PvPvE шутер у стилі Дикого Заходу з монстрами. Напружені бої трійками у відкритому світі.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/594650/header.jpg'],

            ['name' => 'Dota 2', 'genre' => 'MOBA', 'maxPlayers' => 5, 'steamAppId' => 570,
                'description' => 'Найскладніша MOBA з понад 120 героями та мільйонними призовими на The International.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/570/header.jpg'],
            ['name' => 'League of Legends', 'genre' => 'MOBA', 'maxPlayers' => 5, 'steamAppId' => null,
                'description' => 'Найпопулярніша MOBA у світі від Riot Games. 160+ чемпіонів, ранковий режим, кіберспорт.',
                'image' => 'https://cdn1.epicgames.com/offer/24b9b5e323bc40eea252a10cdd3b2f10/EGS_LeagueofLegends_RiotGames_S1_2560x1440-80471666c140f790f28dff68d72c384b'],
            ['name' => 'Smite 2', 'genre' => 'MOBA', 'maxPlayers' => 5, 'steamAppId' => null,
                'description' => 'MOBA від третьої особи з богами та міфічними істотами. Унікальний геймплей від Hi-Rez Studios.',
                'image' => 'https://webcdn.hirezstudios.com/smite2/wp-content/uploads/2024/01/SMITE_2_logo_stacked_color_white_1.png'],

            ['name' => 'Apex Legends', 'genre' => 'Battle Royale', 'maxPlayers' => 3, 'steamAppId' => 1172470,
                'description' => 'Динамічний Battle Royale на 60 гравців з легендами та їхніми здібностями. Швидкий темп і командна гра.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/1172470/header.jpg'],
            ['name' => 'Fortnite', 'genre' => 'Battle Royale', 'maxPlayers' => 4, 'steamAppId' => null,
                'description' => 'Battle Royale з будівництвом на 100 гравців. Регулярні оновлення, колаборації та творчий режим.',
                'image' => 'https://cdn2.unrealengine.com/fortnite-chapter-4-season-4-keyart-3840x2160-3840x2160-2a2b41c37baa.jpg'],
            ['name' => 'PUBG: Battlegrounds', 'genre' => 'Battle Royale', 'maxPlayers' => 4, 'steamAppId' => 578080,
                'description' => 'Класичний Battle Royale на 100 гравців. Реалістична стрільба, величезні карти, соло або командна гра.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/578080/header.jpg'],

            ['name' => 'Minecraft', 'genre' => 'Survival', 'maxPlayers' => 20, 'steamAppId' => null,
                'description' => 'Пісочниця без обмежень. Будуйте, досліджуйте, виживайте у нескінченному блоковому світі з друзями.',
                'image' => 'https://www.minecraft.net/content/dam/games/minecraft/key-art/MC-Vanilla_The-Wild-Update_key-art_540x300.jpg'],
            ['name' => 'Rust', 'genre' => 'Survival', 'maxPlayers' => 10, 'steamAppId' => 252490,
                'description' => 'Хардкорне PvP виживання. Збирайте ресурси, будуйте базу, захищайтесь від інших гравців.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/252490/header.jpg'],
            ['name' => 'ARK: Survival Ascended', 'genre' => 'Survival', 'maxPlayers' => 10, 'steamAppId' => 2399830,
                'description' => 'Виживання серед динозаврів на Unreal Engine 5. Приручайте тварин, будуйте бази, грайте з друзями.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/2399830/header.jpg'],
            ['name' => 'Valheim', 'genre' => 'Survival', 'maxPlayers' => 10, 'steamAppId' => 892970,
                'description' => 'Кооперативне виживання у скандинавському сетингу. Будівництво, бої з босами, дослідження.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/892970/header.jpg'],
            ['name' => 'The Forest', 'genre' => 'Survival', 'maxPlayers' => 8, 'steamAppId' => 242760,
                'description' => 'Виживання у лісі після авіакатастрофи. Будуйте укриття, досліджуйте печери, уникайте мутантів.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/242760/header.jpg'],
            ['name' => 'Terraria', 'genre' => 'Survival', 'maxPlayers' => 8, 'steamAppId' => 105600,
                'description' => '2D пісочниця з елементами RPG. Копайте, будуйте, битесь з босами у процедурно-генерованому світі.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/105600/header.jpg'],

            ['name' => 'World of Warcraft', 'genre' => 'MMO', 'maxPlayers' => 40, 'steamAppId' => null,
                'description' => 'Легендарна MMORPG від Blizzard з 20-річною історією. Рейди, данжони, PvP арени, епічний сюжет.',
                'image' => 'https://blz-contentstack-images.akamaized.net/v3/assets/blt3452e3b114fab0cd/bltd4aaa9d498b2c508/WoW_The_War_Within_Key_Art_16x9.webp'],
            ['name' => 'Final Fantasy XIV', 'genre' => 'MMO', 'maxPlayers' => 8, 'steamAppId' => 39210,
                'description' => 'Популярна MMORPG від Square Enix з неймовірним сюжетом, рейдами та крафтингом.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/39210/header.jpg'],
            ['name' => 'Lost Ark', 'genre' => 'MMO', 'maxPlayers' => 8, 'steamAppId' => 1599340,
                'description' => 'Безкоштовна ARPG MMO з епічними рейдами, класами та ізометричним геймплеєм.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/1599340/header.jpg'],
            ['name' => 'Path of Exile 2', 'genre' => 'MMO', 'maxPlayers' => 6, 'steamAppId' => null,
                'description' => 'Хардкорне Action RPG з безмежним крафтингом білдів. Духовний наступник Diablo 2.',
                'image' => 'https://web.poecdn.com/protected/image/poe2/hero/PoE2-Open-Graph.jpg'],

            ['name' => 'Rocket League', 'genre' => 'Sports', 'maxPlayers' => 4, 'steamAppId' => 252950,
                'description' => 'Футбол на реактивних машинах. Швидкий аркадний геймплей з високою стелею майстерності.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/252950/header.jpg'],
            ['name' => 'EA FC 25', 'genre' => 'Sports', 'maxPlayers' => 2, 'steamAppId' => null,
                'description' => 'Найпопулярніший футбольний симулятор. Ultimate Team, кар\'єра, онлайн-матчі з друзями.',
                'image' => 'https://media.contentapi.ea.com/content/dam/ea/fc/fc-25/common/featured-image/fc25-featured-image-16x9.jpg.adapt.crop191x100.1200w.jpg'],

            ['name' => 'Civilization VI', 'genre' => 'Strategy', 'maxPlayers' => 12, 'steamAppId' => 289070,
                'description' => 'Покрокова стратегія — побудуйте цивілізацію від стародавності до космічної ери.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/289070/header.jpg'],
            ['name' => 'Age of Empires IV', 'genre' => 'Strategy', 'maxPlayers' => 8, 'steamAppId' => 1466860,
                'description' => 'RTS від Microsoft — керуйте арміями середньовічних цивілізацій у стратегічних битвах.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/1466860/header.jpg'],
            ['name' => 'Hearts of Iron IV', 'genre' => 'Strategy', 'maxPlayers' => 32, 'steamAppId' => 394360,
                'description' => 'Глобальна стратегія Другої світової війни. Керуйте країною, економікою та армією.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/394360/header.jpg'],

            ['name' => 'Lethal Company', 'genre' => 'Co-op', 'maxPlayers' => 4, 'steamAppId' => 1966720,
                'description' => 'Кооперативний хоррор — збирайте лут на покинутих планетах, уникаючи монстрів.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/1966720/header.jpg'],
            ['name' => 'Phasmophobia', 'genre' => 'Co-op', 'maxPlayers' => 4, 'steamAppId' => 739630,
                'description' => 'Кооперативне полювання на привидів з реальним розпізнаванням голосу.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/739630/header.jpg'],
            ['name' => 'Deep Rock Galactic', 'genre' => 'Co-op', 'maxPlayers' => 4, 'steamAppId' => 548430,
                'description' => 'Кооперативний шутер — грайте за гномів-шахтарів у процедурно-генерованих печерах.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/548430/header.jpg'],
            ['name' => 'Left 4 Dead 2', 'genre' => 'Co-op', 'maxPlayers' => 4, 'steamAppId' => 550,
                'description' => 'Класичний кооперативний зомбі-шутер від Valve на 4 гравців. Кампанії та PvP режим.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/550/header.jpg'],

            ['name' => 'Mortal Kombat 1', 'genre' => 'Fighting', 'maxPlayers' => 2, 'steamAppId' => 1971870,
                'description' => 'Культовий файтинг з брутальними фаталіті. Нова ера всесвіту Mortal Kombat.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/1971870/header.jpg'],
            ['name' => 'Street Fighter 6', 'genre' => 'Fighting', 'maxPlayers' => 2, 'steamAppId' => 1364780,
                'description' => 'Легендарний файтинг від Capcom з новою системою Drive та відкритим світом.',
                'image' => 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/1364780/header.jpg'],
        ];

        foreach ($games as $data) {
            $existing = $gameRepo->findOneBy(['name' => $data['name']]);
            if ($existing) {
                // Update missing data
                if (!$existing->getImageUrl() && !empty($data['image'])) {
                    $existing->setImageUrl($data['image']);
                }
                if ((!$existing->getDescription() || strlen($existing->getDescription()) < 30) && !empty($data['description'])) {
                    $existing->setDescription($data['description']);
                }
                continue;
            }

            $game = new Game();
            $game->setName($data['name']);
            $game->setGenre($data['genre']);
            $game->setMaxPlayers($data['maxPlayers']);
            $game->setSteamAppId($data['steamAppId']);
            $game->setDescription($data['description']);
            $game->setImageUrl($data['image']);
            $game->setSlug(strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($data['name']))));
            $manager->persist($game);
        }

        $manager->flush();
    }
}
