<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20180214094433 extends AbstractMigration
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
        
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_message DROP FOREIGN KEY FK_BF1B821A2FB3D0EE');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_message ADD CONSTRAINT FK_BF1B821A2FB3D0EE FOREIGN KEY (project) REFERENCES mindscreen_projectpackages_domain_model_project (persistence_object_identifier) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package DROP FOREIGN KEY FK_D7CED5F02FB3D0EE');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package ADD CONSTRAINT FK_D7CED5F02FB3D0EE FOREIGN KEY (project) REFERENCES mindscreen_projectpackages_domain_model_project (persistence_object_identifier) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_project DROP FOREIGN KEY FK_2615628B5CFE57CD');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_project ADD CONSTRAINT FK_2615628B5CFE57CD FOREIGN KEY (repository) REFERENCES mindscreen_projectpackages_domain_model_repository (persistence_object_identifier) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_message DROP FOREIGN KEY FK_BF1B821A2FB3D0EE');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_message ADD CONSTRAINT FK_BF1B821A2FB3D0EE FOREIGN KEY (project) REFERENCES mindscreen_projectpackages_domain_model_project (persistence_object_identifier)');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package DROP FOREIGN KEY FK_D7CED5F02FB3D0EE');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package ADD CONSTRAINT FK_D7CED5F02FB3D0EE FOREIGN KEY (project) REFERENCES mindscreen_projectpackages_domain_model_project (persistence_object_identifier)');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_project DROP FOREIGN KEY FK_2615628B5CFE57CD');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_project ADD CONSTRAINT FK_2615628B5CFE57CD FOREIGN KEY (repository) REFERENCES mindscreen_projectpackages_domain_model_repository (persistence_object_identifier)');
    }
}