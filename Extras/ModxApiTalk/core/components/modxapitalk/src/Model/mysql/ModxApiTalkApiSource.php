<?php
namespace ModxApiTalk\Model\mysql;

use xPDO\xPDO;

class ModxApiTalkApiSource extends \ModxApiTalk\Model\ModxApiTalkApiSource
{

    public static $metaMap = array (
        'package' => 'ModxApiTalk\\Model',
        'version' => '3.0',
        'table' => 'modxapitalk_sources',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'name' => NULL,
            'description' => NULL,
            'url' => NULL,
            'auth_type' => 'none',
            'auth_value' => NULL,
            'params' => NULL,
            'extract_keys' => NULL,
            'createdon' => NULL,
            'updatedon' => NULL,
        ),
        'fieldMeta' => 
        array (
            'name' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
            ),
            'description' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
            ),
            'url' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
            ),
            'auth_type' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '50',
                'phptype' => 'string',
                'default' => 'none',
            ),
            'auth_value' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
            ),
            'params' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
            ),
            'extract_keys' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
            ),
            'createdon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
            ),
            'updatedon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
            ),
        ),
        'indexes' => 
        array (
            'name' => 
            array (
                'alias' => 'name',
                'primary' => false,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'name' => 
                    array (
                    ),
                ),
            ),
        ),
    );

}
