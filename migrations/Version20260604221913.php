<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260604221913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set ON DELETE SET NULL for review.lobby_id FK';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6B6612FD9');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B6612FD9 FOREIGN KEY (lobby_id) REFERENCES lobby (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6B6612FD9');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B6612FD9 FOREIGN KEY (lobby_id) REFERENCES lobby (id)');
    }
}
