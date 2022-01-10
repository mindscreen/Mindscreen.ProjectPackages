<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20180222172325 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription(): string 
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema): void 
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        
        $this->addSql('CREATE TABLE mindscreen_projectpackages_domain_model_59dec_dependencies_join (projectpackages_package VARCHAR(40) NOT NULL, PRIMARY KEY(projectpackages_package)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_59dec_dependencies_join ADD CONSTRAINT FK_D31F101B6F8EF12D FOREIGN KEY (projectpackages_package) REFERENCES mindscreen_projectpackages_domain_model_package (persistence_object_identifier)');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_message CHANGE message message LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package DROP FOREIGN KEY FK_D7CED5F048B5DEF7');
        $this->addSql('DROP INDEX IDX_D7CED5F048B5DEF7 ON mindscreen_projectpackages_domain_model_package');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package DROP requiredby, CHANGE depth depth INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void 
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        
        $this->addSql('DROP TABLE mindscreen_projectpackages_domain_model_59dec_dependencies_join');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_message CHANGE message message LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package ADD requiredby VARCHAR(40) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE depth depth INT NOT NULL');
        $this->addSql('ALTER TABLE mindscreen_projectpackages_domain_model_package ADD CONSTRAINT FK_D7CED5F048B5DEF7 FOREIGN KEY (requiredby) REFERENCES mindscreen_projectpackages_domain_model_package (persistence_object_identifier) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D7CED5F048B5DEF7 ON mindscreen_projectpackages_domain_model_package (requiredby)');
    }
}