<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130816175207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
         $this->addSQL("
            INSERT INTO `file_group` (`id`, `name`, `code`, `public`) VALUES (6, '临时文件夹', 'tmp', 1);
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs

    }
}
