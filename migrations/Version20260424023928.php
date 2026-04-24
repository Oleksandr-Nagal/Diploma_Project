<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424023928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE report (id INT AUTO_INCREMENT NOT NULL, reason VARCHAR(500) NOT NULL, status VARCHAR(20) NOT NULL, moderator_note VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, resolved_at DATETIME DEFAULT NULL, reporter_id INT NOT NULL, reported_user_id INT DEFAULT NULL, reported_message_id INT DEFAULT NULL, reported_review_id INT DEFAULT NULL, resolved_by_id INT DEFAULT NULL, INDEX IDX_C42F7784E1CFE6F5 (reporter_id), INDEX IDX_C42F7784E7566E (reported_user_id), INDEX IDX_C42F7784387BD835 (reported_message_id), INDEX IDX_C42F778454B47ECB (reported_review_id), INDEX IDX_C42F77846713A32B (resolved_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784E7566E FOREIGN KEY (reported_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784387BD835 FOREIGN KEY (reported_message_id) REFERENCES chat_message (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778454B47ECB FOREIGN KEY (reported_review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77846713A32B FOREIGN KEY (resolved_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE achievement ADD CONSTRAINT FK_96737FF1A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE achievement ADD CONSTRAINT FK_96737FF1E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16F624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16B6612FD9 FOREIGN KEY (lobby_id) REFERENCES lobby (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC1671F7E88B FOREIGN KEY (event_id) REFERENCES game_event (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16E92F8F78 FOREIGN KEY (recipient_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45FED442CF4 FOREIGN KEY (requester_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE game_event ADD CONSTRAINT FK_99D7328876C4DDA FOREIGN KEY (organizer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE game_event ADD CONSTRAINT FK_99D7328E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE event_participants ADD CONSTRAINT FK_9C7A7A61DAB317AD FOREIGN KEY (game_event_id) REFERENCES game_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_participants ADD CONSTRAINT FK_9C7A7A61A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lobby ADD CONSTRAINT FK_CCE455F7E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE lobby ADD CONSTRAINT FK_CCE455F77E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE lobby_member ADD CONSTRAINT FK_9582B049B6612FD9 FOREIGN KEY (lobby_id) REFERENCES lobby (id)');
        $this->addSql('ALTER TABLE lobby_member ADD CONSTRAINT FK_9582B049A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6158E0B66 FOREIGN KEY (target_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B6612FD9 FOREIGN KEY (lobby_id) REFERENCES lobby (id)');
        $this->addSql('ALTER TABLE user ADD banned_until DATETIME DEFAULT NULL, CHANGE rating rating DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784E1CFE6F5');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784E7566E');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784387BD835');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F778454B47ECB');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77846713A32B');
        $this->addSql('DROP TABLE report');
        $this->addSql('ALTER TABLE achievement DROP FOREIGN KEY FK_96737FF1A76ED395');
        $this->addSql('ALTER TABLE achievement DROP FOREIGN KEY FK_96737FF1E48FD905');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16F624B39D');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16B6612FD9');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC1671F7E88B');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16E92F8F78');
        $this->addSql('ALTER TABLE event_participants DROP FOREIGN KEY FK_9C7A7A61DAB317AD');
        $this->addSql('ALTER TABLE event_participants DROP FOREIGN KEY FK_9C7A7A61A76ED395');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FED442CF4');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FCD53EDB6');
        $this->addSql('ALTER TABLE game_event DROP FOREIGN KEY FK_99D7328876C4DDA');
        $this->addSql('ALTER TABLE game_event DROP FOREIGN KEY FK_99D7328E48FD905');
        $this->addSql('ALTER TABLE lobby DROP FOREIGN KEY FK_CCE455F7E48FD905');
        $this->addSql('ALTER TABLE lobby DROP FOREIGN KEY FK_CCE455F77E3C61F9');
        $this->addSql('ALTER TABLE lobby_member DROP FOREIGN KEY FK_9582B049B6612FD9');
        $this->addSql('ALTER TABLE lobby_member DROP FOREIGN KEY FK_9582B049A76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6F675F31B');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6158E0B66');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6B6612FD9');
        $this->addSql('ALTER TABLE `user` DROP banned_until, CHANGE rating rating DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
