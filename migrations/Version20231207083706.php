<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231207083706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier DROP INDEX UNIQ_24CC0DF2A76ED395, ADD INDEX IDX_24CC0DF2A76ED395 (user_id)');
        $this->addSql('ALTER TABLE panier CHANGE user_id user_id INT NOT NULL, CHANGE date_achat date_achat DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier DROP INDEX IDX_24CC0DF2A76ED395, ADD UNIQUE INDEX UNIQ_24CC0DF2A76ED395 (user_id)');
        $this->addSql('ALTER TABLE panier CHANGE user_id user_id INT DEFAULT NULL, CHANGE date_achat date_achat DATETIME NOT NULL');
    }
}
