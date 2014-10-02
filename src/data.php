<?php

use Ideys\SilexHooks;
use Doctrine\DBAL\Schema\Table;

$db = SilexHooks::db($app);
$schema = $db->getSchemaManager();

if (!$schema->tablesExist('expose_user')) {
    $table = new Table('expose_user');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('username', 'string', array('length' => 32));
    $table->addColumn('email', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('website', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('phone', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('mobile', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('address', 'string', array('length' => 500, 'default' => null, 'notnull' => false));
    $table->addColumn('gender', 'string', array('length' => 1));
    $table->addColumn('firstname', 'string', array('length' => 255));
    $table->addColumn('lastname', 'string', array('length' => 255));
    $table->addColumn('organization', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addUniqueIndex(array('username'));
    $table->addColumn('password', 'string', array('length' => 255));
    $table->addColumn('roles', 'string', array('length' => 255));
    $table->addColumn('lastLogin', 'datetime', array('default' => null, 'notnull' => false));

    $schema->createTable($table);

    // User profiles demo (password: hello world)
    $db->insert('expose_user', array(
      'username' => 'user',
      'email' => 'user@expose.ideys.com',
      'gender' => 'm',
      'firstname' => 'Marc',
      'lastname' => 'Assein',
      'password' => 'SK4iRARs2ASrC5yITatgnTmgwmI4zVfJvl/0X6fkKo5s4/DyN1k63kTbw63HMCP9bwCXd1IYBwZm9TAmhAgo+w==',
      'roles' => serialize(array('ROLE_USER')),
    ));
    $db->insert('expose_user', array(
      'username' => 'editor',
      'email' => 'editor@expose.ideys.com',
      'gender' => 'f',
      'firstname' => 'Mathilde',
      'lastname' => 'Sellier',
      'password' => 'SK4iRARs2ASrC5yITatgnTmgwmI4zVfJvl/0X6fkKo5s4/DyN1k63kTbw63HMCP9bwCXd1IYBwZm9TAmhAgo+w==',
      'roles' => serialize(array('ROLE_EDITOR')),
    ));
    $db->insert('expose_user', array(
      'username' => 'admin',
      'email' => 'admin@expose.ideys.com',
      'gender' => 'f',
      'firstname' => 'Nathalie',
      'lastname' => 'Chamitang',
      'password' => 'SK4iRARs2ASrC5yITatgnTmgwmI4zVfJvl/0X6fkKo5s4/DyN1k63kTbw63HMCP9bwCXd1IYBwZm9TAmhAgo+w==',
      'roles' => serialize(array('ROLE_ADMIN')),
    ));
    $db->insert('expose_user', array(
      'username' => 'superadmin',
      'email' => 'superadmin@expose.ideys.com',
      'gender' => 'm',
      'firstname' => 'John',
      'lastname' => 'Doe',
      'password' => 'SK4iRARs2ASrC5yITatgnTmgwmI4zVfJvl/0X6fkKo5s4/DyN1k63kTbw63HMCP9bwCXd1IYBwZm9TAmhAgo+w==',
      'roles' => serialize(array('ROLE_SUPER_ADMIN')),
    ));
}

if (!$schema->tablesExist('expose_section')) {
    $table = new Table('expose_section');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('expose_section_id', 'integer', array('unsigned' => true, 'default' => null, 'notnull' => false));
    $table->addIndex(array('expose_section_id'));
    $table->addColumn('connected_sections_id', 'array', array('length' => 500, 'default' => null, 'notnull' => false));
    $table->addColumn('type', 'string', array('length' => 32));
    $table->addColumn('slug', 'string', array('length' => 255));
    $table->addColumn('custom_css', 'text', array('default' => null, 'notnull' => false));
    $table->addColumn('custom_js', 'text', array('default' => null, 'notnull' => false));
    $table->addColumn('archive', 'boolean');
    $table->addColumn('tag', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('menu_pos', 'string', array('length' => 32));
    $table->addColumn('target_blank', 'boolean');
    $table->addColumn('visibility', 'string', array('length' => 32));
    $table->addColumn('shuffle', 'boolean');
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
    $table->addColumn('legend', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('parameters', 'object', array('default' => null, 'notnull' => false));
    $table->addColumn('language', 'string', array('length' => 5));

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_section_item')) {
    $table = new Table('expose_section_item');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('expose_section_id', 'integer', array('unsigned' => true, 'default' => null, 'notnull' => false));
    $table->addIndex(array('expose_section_id'));
    $table->addColumn('type', 'string', array('length' => 55));
    $table->addColumn('category', 'string', array('length' => 55, 'default' => null, 'notnull' => false));
    $table->addColumn('tags', 'string', array('length' => 500, 'default' => null, 'notnull' => false));
    $table->addColumn('slug', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('path', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('posting_date', 'datetime', array('default' => null, 'notnull' => false));
    $table->addColumn('author', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('latitude', 'float', array('scale' => 7, 'precision' => 20, 'default' => null, 'notnull' => false));
    $table->addColumn('longitude', 'float', array('scale' => 7, 'precision' => 20, 'default' => null, 'notnull' => false));
    $table->addColumn('published', 'boolean');
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
    $table->addColumn('title', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('description', 'string', array('length' => 500, 'default' => null, 'notnull' => false));
    $table->addColumn('content', 'text', array('default' => null, 'notnull' => false));
    $table->addColumn('link', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('parameters', 'object', array('default' => null, 'notnull' => false));
    $table->addColumn('language', 'string', array('length' => 5));

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_form_result')) {
    $table = new Table('expose_form_result');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('expose_section_id', 'integer', array('unsigned' => true));
    $table->addIndex(array('expose_section_id'));
    $table->addColumn('result', 'text');
    $table->addColumn('language', 'string', array('length' => 5));
    $table->addColumn('date', 'datetime');

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_messaging')) {
    $table = new Table('expose_messaging');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('name', 'string', array('length' => 255));
    $table->addColumn('email', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('subject', 'string', array('length' => 255, 'default' => null, 'notnull' => false));
    $table->addColumn('message', 'text');
    $table->addColumn('date', 'datetime');
    $table->addColumn('read_at', 'datetime', array('default' => null, 'notnull' => false));
    $table->addColumn('archive', 'boolean');

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_files')) {
    $table = new Table('expose_files');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('file', 'string', array('length' => 255));
    $table->addColumn('mime', 'string', array('length' => 55));
    $table->addColumn('title', 'string', array('length' => 255));
    $table->addColumn('name', 'string', array('length' => 255));
    $table->addColumn('slug', 'string', array('length' => 255));
    blameAndTimestampSchema($table);

    $schema->createTable($table);
}

if (!$schema->tablesExist('expose_files_recipients')) {
    $table = new Table('expose_files_recipients');
    $table->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $table->setPrimaryKey(array('id'));
    $table->addColumn('expose_files_id', 'integer', array('unsigned' => true));
    $table->addIndex(array('expose_files_id'));
    $table->addColumn('name', 'string', array('length' => 255));
    $table->addColumn('token', 'string', array('length' => 255));
    $table->addColumn('download_counter', 'integer');
    $table->addColumn('download_logs', 'text');

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
