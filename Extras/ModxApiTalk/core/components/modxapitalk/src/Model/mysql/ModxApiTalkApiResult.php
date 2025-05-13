<?php
namespace ModxApiTalk\Model\mysql;

use xPDO\xPDO;

class ModxApiTalkApiResult extends \ModxApiTalk\Model\ModxApiTalkApiResult
{

    public static $metaMap = array (
        'package' => 'ModxApiTalk\\Model',
        'version' => '3.0',
        'table' => 'modxapitalk_results',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'source_id' => NULL,
            'response' => NULL,
            'createdon' => NULL,
        ),
        'fieldMeta' => 
        array (
            'source_id' => 
            array (
                'dbtype' => 'int',
                'phptype' => 'integer',
            ),
            'response' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
            ),
            'createdon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
            ),
        ),
        'indexes' => 
        array (
            'source' => 
            array (
                'alias' => 'source',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'source_id' => 
                    array (
                    ),
                ),
            ),
        ),
    );

}
