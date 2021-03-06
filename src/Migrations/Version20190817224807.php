<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190817224807 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE anime (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, image_url VARCHAR(255) NOT NULL, episodes_count INT NOT NULL, airing TINYINT(1) NOT NULL, score DOUBLE PRECISION NOT NULL, mal_id INT NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recommendation (id INT AUTO_INCREMENT NOT NULL, anime_id INT NOT NULL, recommended_id INT NOT NULL, recommendation_count INT NOT NULL, INDEX IDX_433224D2794BBE89 (anime_id), INDEX IDX_433224D270C20237 (recommended_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id)');
        $this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D270C20237 FOREIGN KEY (recommended_id) REFERENCES anime (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D2794BBE89');
        $this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D270C20237');
        $this->addSql('DROP TABLE anime');
        $this->addSql('DROP TABLE recommendation');
    }
}
