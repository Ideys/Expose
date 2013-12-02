<?php

use Doctrine\DBAL\Schema\Table;

$schema = $app['db']->getSchemaManager();

if (!$schema->tablesExist('expose_user')) {
    $table = new Table('expose_user');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('username', 'string', array('length' => 32));
    $table->addUniqueIndex(array('username'));
    $table->addColumn('password', 'string', array('length' => 255));
    $table->addColumn('roles', 'string', array('length' => 255));

    $schema->createTable($table);

    // Admin demo: admin admin
    $app['db']->insert('expose_user', array(
      'username' => 'admin',
      'password' => 'nhDr7OyKlXQju+Ge/WKGrPQ9lPBSUFfpK+B1xqx/+8zLZqRNX0+5G1zBQklXUFy86lCpkAofsExlXiorUcKSNQ==',
      'roles' => 'ROLE_ADMIN'
    ));
}

if (!$schema->tablesExist('expose_section')) {
    $table = new Table('expose_section');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('expose_section_id', 'integer', array('unsigned' => true, 'default' => null, 'notnull' => false));
    $table->addIndex(array('expose_section_id'));
    $table->addColumn('type', 'string', array('length' => 32));
    $table->addColumn('slug', 'string', array('length' => 255));
    $table->addColumn('active', 'boolean');
    $table->addColumn('hierarchy', 'smallint');
    blameAndTimestampSchema($table);

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_section_trans')) {
    $table = new Table('expose_section_trans');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('expose_section_id', 'integer', array('unsigned' => true));
    $table->addIndex(array('expose_section_id'));
    $table->addColumn('title', 'string', array('length' => 255));
    $table->addColumn('description', 'string', array('length' => 500, 'default' => null, 'notnull' => false));
    $table->addColumn('language', 'string', array('length' => 5));

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_section_item')) {
    $table = new Table('expose_section_item');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('expose_section_id', 'integer', array('unsigned' => true));
    $table->addIndex(array('expose_section_id'));
    $table->addColumn('type', 'string', array('length' => 55));
    $table->addColumn('path', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('hierarchy', 'smallint');
    blameAndTimestampSchema($table);

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_section_item_trans')) {
    $table = new Table('expose_section_item_trans');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('expose_section_item_id', 'integer', array('unsigned' => true));
    $table->addIndex(array('expose_section_item_id'));
    $table->addColumn('title', 'string', array('length' => 255));
    $table->addColumn('description', 'string', array('length' => 500, 'default' => null, 'notnull' => false));
    $table->addColumn('content', 'text', array('default' => null, 'notnull' => false));
    $table->addColumn('language', 'string', array('length' => 5));

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_messaging')) {
    $table = new Table('expose_messaging');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('name', 'string', array('length' => 255));
    $table->addColumn('email', 'string', array('length' => 255));
    $table->addColumn('message', 'text');
    $table->addColumn('date', 'datetime');

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_settings')) {
    $table = new Table('expose_settings');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('attribute', 'string', array('length' => 255));
    $table->addUniqueIndex(array('attribute'));
    $table->addColumn('value', 'text', array('default' => null, 'notnull' => false));

    $schema->createTable($table);
}

function blameAndTimestampSchema(Table $table)
{
    $table->addColumn('created_by', 'integer', array('unsigned' => true, 'default' => null, 'notnull' => false));
    $table->addIndex(array('created_by'));
    $table->addColumn('updated_by', 'integer', array('unsigned' => true, 'default' => null, 'notnull' => false));
    $table->addIndex(array('updated_by'));
    $table->addColumn('created_at', 'datetime');
    $table->addColumn('updated_at', 'datetime');
}
