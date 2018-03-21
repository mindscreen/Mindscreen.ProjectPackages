<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20180213163908 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('DROP TABLE IF EXISTS mindscreen_projectpackages_domain_model_message');
        $this->addSql('DROP TABLE IF EXISTS mindscreen_projectpackages_domain_model_repository');
        $this->addSql('DROP TABLE IF EXISTS mindscreen_projectpackages_domain_model_package');
        $this->addSql('DROP TABLE IF EXISTS mindscreen_projectpackages_domain_model_project');
        $this->addSql('CREATE TABLE mindscreen_projectpackages_domain_model_message (persistence_object_identifier VARCHAR(40) NOT NULL, project VARCHAR(40) DEFAULT NULL, message VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, severity INT NOT NULL, arguments LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_BF1B821A2FB3D0EE (project), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mindscreen_projectpackages_domain_model_repository (persistence_object_identifier VARCHAR(40) NOT NULL, id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, namespace VARCHAR(255) NOT NULL, weburl VARCHAR(255) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, defaultbranch VARCHAR(255) DEFAULT NULL, repositorysource VARCHAR(255) NOT NULL, PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mindscreen_projectpackages_domain_model_package (persistence_object_identifier VARCHAR(40) NOT NULL, project VARCHAR(40) DEFAULT NULL, name VARCHAR(255) NOT NULL, version VARCHAR(255) NOT NULL, additional LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', packagemanager VARCHAR(255) NOT NULL, INDEX IDX_D7CED5F02FB3D0EE (project), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mindscreen_projectpackages_domain_model_project (persistence_object_identifier VARCHAR(40) NOT NULL, repository VARCHAR(40) DEFAULT NULL, `key` VARCHAR(32) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, additional LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', packagemanager VARCHAR(255) NOT NULL, updated DATETIME NOT NULL, INDEX IDX_2615628B5CFE57CD (repository), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_message ADD CONSTRAINT FK_BF1B821A2FB3D0EE FOREIGN KEY (project) REFERENCES mindscreen_projectpackages_domain_model_project (persistence_object_identifier)');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package ADD CONSTRAINT FK_D7CED5F02FB3D0EE FOREIGN KEY (project) REFERENCES mindscreen_projectpackages_domain_model_project (persistence_object_identifier)');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_project ADD CONSTRAINT FK_2615628B5CFE57CD FOREIGN KEY (repository) REFERENCES mindscreen_projectpackages_domain_model_repository (persistence_object_identifier)');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_project DROP FOREIGN KEY FK_2615628B5CFE57CD');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_message DROP FOREIGN KEY FK_BF1B821A2FB3D0EE');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package DROP FOREIGN KEY FK_D7CED5F02FB3D0EE');
        $this->addSql('DROP TABLE mindscreen_projectpackages_domain_model_message');
        $this->addSql('DROP TABLE mindscreen_projectpackages_domain_model_repository');
        $this->addSql('DROP TABLE mindscreen_projectpackages_domain_model_package');
        $this->addSql('DROP TABLE mindscreen_projectpackages_domain_model_project');
    }
}