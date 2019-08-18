<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190817231029 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE anime_genre (anime_id INT NOT NULL, genre_id INT NOT NULL, INDEX IDX_EFF953C7794BBE89 (anime_id), INDEX IDX_EFF953C74296D31F (genre_id), PRIMARY KEY(anime_id, genre_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, mal_id INT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, INDEX mal_genre_idx (mal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE anime_genre ADD CONSTRAINT FK_EFF953C7794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE anime_genre ADD CONSTRAINT FK_EFF953C74296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE anime ADD trailer_url VARCHAR(255) DEFAULT NULL, ADD title_english VARCHAR(255) DEFAULT NULL, ADD title_japanese VARCHAR(255) DEFAULT NULL, ADD status VARCHAR(255) NOT NULL, ADD aired_from DATETIME NOT NULL, ADD aired_to DATETIME DEFAULT NULL, ADD duration VARCHAR(255) DEFAULT NULL, ADD rating VARCHAR(255) NOT NULL, ADD synonyms JSON NOT NULL COMMENT \'(DC2Type:json_array)\', ADD type VARCHAR(255) NOT NULL, ADD rank INT NOT NULL, ADD popularity INT NOT NULL, ADD opening_themes JSON NOT NULL COMMENT \'(DC2Type:json_array)\', ADD ending_themes JSON NOT NULL COMMENT \'(DC2Type:json_array)\', ADD broadcast VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX mal_anime_idx ON anime (mal_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE anime_genre DROP FOREIGN KEY FK_EFF953C74296D31F');
        $this->addSql('DROP TABLE anime_genre');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP INDEX mal_anime_idx ON anime');
        $this->addSql('ALTER TABLE anime DROP trailer_url, DROP title_english, DROP title_japanese, DROP status, DROP aired_from, DROP aired_to, DROP duration, DROP rating, DROP synonyms, DROP type, DROP rank, DROP popularity, DROP opening_themes, DROP ending_themes, DROP broadcast');
    }
}
