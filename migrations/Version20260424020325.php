<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424020325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE achievement (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(200) NOT NULL, description VARCHAR(500) DEFAULT NULL, icon_url VARCHAR(255) DEFAULT NULL, steam_achievement_id VARCHAR(100) DEFAULT NULL, unlocked_at DATETIME DEFAULT NULL, user_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_96737FF1A76ED395 (user_id), INDEX IDX_96737FF1E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, attachment_url VARCHAR(255) DEFAULT NULL, is_private TINYINT NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, lobby_id INT DEFAULT NULL, event_id INT DEFAULT NULL, recipient_id INT DEFAULT NULL, INDEX IDX_FAB3FC16F624B39D (sender_id), INDEX IDX_FAB3FC16B6612FD9 (lobby_id), INDEX IDX_FAB3FC1671F7E88B (event_id), INDEX IDX_FAB3FC16E92F8F78 (recipient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE friendship (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, accepted_at DATETIME DEFAULT NULL, requester_id INT NOT NULL, receiver_id INT NOT NULL, INDEX IDX_7234A45FED442CF4 (requester_id), INDEX IDX_7234A45FCD53EDB6 (receiver_id), UNIQUE INDEX UNIQ_7234A45FED442CF4CD53EDB6 (requester_id, receiver_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(255) DEFAULT NULL, genre VARCHAR(50) NOT NULL, description VARCHAR(500) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, max_players INT DEFAULT NULL, steam_app_id INT DEFAULT NULL, is_active TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game_event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, start_at DATETIME NOT NULL, end_at DATETIME DEFAULT NULL, max_participants INT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, organizer_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_99D7328876C4DDA (organizer_id), INDEX IDX_99D7328E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event_participants (game_event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_9C7A7A61DAB317AD (game_event_id), INDEX IDX_9C7A7A61A76ED395 (user_id), PRIMARY KEY (game_event_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lobby (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, description VARCHAR(500) DEFAULT NULL, max_members INT NOT NULL, skill_level VARCHAR(20) NOT NULL, min_age INT DEFAULT NULL, max_age INT DEFAULT NULL, language VARCHAR(10) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, status VARCHAR(20) NOT NULL, is_private TINYINT NOT NULL, voice_chat TINYINT NOT NULL, created_at DATETIME NOT NULL, scheduled_at DATETIME DEFAULT NULL, game_id INT NOT NULL, owner_id INT NOT NULL, INDEX IDX_CCE455F7E48FD905 (game_id), INDEX IDX_CCE455F77E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lobby_member (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, role VARCHAR(20) NOT NULL, joined_at DATETIME NOT NULL, lobby_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_9582B049B6612FD9 (lobby_id), INDEX IDX_9582B049A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, message VARCHAR(500) NOT NULL, link VARCHAR(255) DEFAULT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE report (id INT AUTO_INCREMENT NOT NULL, reason VARCHAR(500) NOT NULL, status VARCHAR(20) NOT NULL, moderator_note VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, resolved_at DATETIME DEFAULT NULL, reporter_id INT NOT NULL, reported_user_id INT DEFAULT NULL, reported_message_id INT DEFAULT NULL, reported_review_id INT DEFAULT NULL, resolved_by_id INT DEFAULT NULL, INDEX IDX_C42F7784E1CFE6F5 (reporter_id), INDEX IDX_C42F7784E7566E (reported_user_id), INDEX IDX_C42F7784387BD835 (reported_message_id), INDEX IDX_C42F778454B47ECB (reported_review_id), INDEX IDX_C42F77846713A32B (resolved_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, is_positive TINYINT NOT NULL, comment VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, author_id INT NOT NULL, target_id INT NOT NULL, lobby_id INT DEFAULT NULL, INDEX IDX_794381C6F675F31B (author_id), INDEX IDX_794381C6158E0B66 (target_id), INDEX IDX_794381C6B6612FD9 (lobby_id), UNIQUE INDEX UNIQ_794381C6F675F31B158E0B66B6612FD9 (author_id, target_id, lobby_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(50) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, bio VARCHAR(500) DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, language VARCHAR(10) DEFAULT NULL, age INT DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, discord_id VARCHAR(255) DEFAULT NULL, steam_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, last_login_at DATETIME DEFAULT NULL, is_premium TINYINT NOT NULL, premium_expires_at DATETIME DEFAULT NULL, is_verified TINYINT NOT NULL, is_banned TINYINT NOT NULL, banned_until DATETIME DEFAULT NULL, rating DOUBLE PRECISION DEFAULT 0 NOT NULL, total_reviews INT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
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
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784E7566E FOREIGN KEY (reported_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784387BD835 FOREIGN KEY (reported_message_id) REFERENCES chat_message (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778454B47ECB FOREIGN KEY (reported_review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77846713A32B FOREIGN KEY (resolved_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6158E0B66 FOREIGN KEY (target_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B6612FD9 FOREIGN KEY (lobby_id) REFERENCES lobby (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE achievement DROP FOREIGN KEY FK_96737FF1A76ED395');
        $this->addSql('ALTER TABLE achievement DROP FOREIGN KEY FK_96737FF1E48FD905');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16F624B39D');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16B6612FD9');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC1671F7E88B');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16E92F8F78');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FED442CF4');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FCD53EDB6');
        $this->addSql('ALTER TABLE game_event DROP FOREIGN KEY FK_99D7328876C4DDA');
        $this->addSql('ALTER TABLE game_event DROP FOREIGN KEY FK_99D7328E48FD905');
        $this->addSql('ALTER TABLE event_participants DROP FOREIGN KEY FK_9C7A7A61DAB317AD');
        $this->addSql('ALTER TABLE event_participants DROP FOREIGN KEY FK_9C7A7A61A76ED395');
        $this->addSql('ALTER TABLE lobby DROP FOREIGN KEY FK_CCE455F7E48FD905');
        $this->addSql('ALTER TABLE lobby DROP FOREIGN KEY FK_CCE455F77E3C61F9');
        $this->addSql('ALTER TABLE lobby_member DROP FOREIGN KEY FK_9582B049B6612FD9');
        $this->addSql('ALTER TABLE lobby_member DROP FOREIGN KEY FK_9582B049A76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784E1CFE6F5');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784E7566E');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784387BD835');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F778454B47ECB');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77846713A32B');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6F675F31B');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6158E0B66');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6B6612FD9');
        $this->addSql('DROP TABLE achievement');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('DROP TABLE friendship');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_event');
        $this->addSql('DROP TABLE event_participants');
        $this->addSql('DROP TABLE lobby');
        $this->addSql('DROP TABLE lobby_member');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE `user`');
    }
}
