<?php

namespace App\Command;

use App\Entity\Game;
use App\Service\SteamGameService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:update-games', description: 'Update games with images and descriptions from Steam')]
class UpdateGamesFromSteamCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private SteamGameService $steamService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $gameRepo = $this->em->getRepository(Game::class);
        $games = $gameRepo->findAll();
        $updated = 0;

        foreach ($games as $game) {
            $needsUpdate = !$game->getImageUrl() || !$game->getDescription();
            if (!$needsUpdate) {
                continue;
            }

            $io->text('Updating: ' . $game->getName());

            if ($game->getSteamAppId()) {
                $details = $this->steamService->getGameDetails($game->getSteamAppId());
                if ($details) {
                    if (!$game->getImageUrl() && !empty($details['image'])) {
                        $game->setImageUrl($details['image']);
                    }
                    if ((!$game->getDescription() || strlen($game->getDescription()) < 20) && !empty($details['description'])) {
                        $game->setDescription($details['description']);
                    }
                    $updated++;
                    $io->text('  -> Steam OK');
                    usleep(300000);
                    continue;
                }
            }

            if (!$game->getImageUrl()) {
                $image = $this->steamService->findImageByName($game->getName());
                if ($image) {
                    $game->setImageUrl($image);
                    $updated++;
                    $io->text('  -> RAWG image OK');
                } else {
                    $io->text('  -> No image found');
                }
                usleep(300000);
            }
        }

        $this->em->flush();
        $io->success("Updated $updated games from Steam.");

        return Command::SUCCESS;
    }
}
