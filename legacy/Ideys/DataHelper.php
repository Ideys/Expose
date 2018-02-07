<?php

namespace Ideys;

use Doctrine\DBAL\Schema\Table;

class DataHelper
{
    /**
     * @param Table $table
     */
    public static function blameAndTimestampSchema(Table $table)
    {
        $table->addColumn('created_by', 'integer', array('unsigned' => true, 'default' => null, 'notnull' => false));
        $table->addIndex(array('created_by'));
        $table->addColumn('updated_by', 'integer', array('unsigned' => true, 'default' => null, 'notnull' => false));
        $table->addIndex(array('updated_by'));
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
    }
}
