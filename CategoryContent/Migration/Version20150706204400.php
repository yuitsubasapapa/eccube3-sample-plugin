<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150706204400 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->createDtbCategoyContentPlugin($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('category_content');
    }

    protected function createDtbCategoyContentPlugin(Schema $schema)
    {
        $table = $schema->createTable("category_content");
        $table->addColumn('category_id', 'integer', array(
                'notnull' => true,
            ));
        $table->addColumn('content', 'text', array(
                'notnull' => true,
            ));
        // Plugin.CategoryContent.Entity.CategoryContent.dcm.ymlがインストール時に効いてくれないので苦肉の策
        $table->addColumn('create_date', 'datetime', array(
                'notnull' => true,
            ));
        $table->addColumn('update_date', 'datetime', array(
                'notnull' => false,
            ));
        $table->setPrimaryKey(array('category_id'));

        $CategoryTable = $schema->getTable("dtb_category");
        $table->addForeignKeyConstraint($CategoryTable,array('category_id'),array('category_id'),array("onDelete" => "CASCADE"));
    }
}