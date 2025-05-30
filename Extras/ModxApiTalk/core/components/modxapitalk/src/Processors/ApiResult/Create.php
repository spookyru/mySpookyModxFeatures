<?php

namespace ModxApiTalk\Processors\ApiResult;

use MODX\Revolution\Processors\Model\CreateProcessor;
use ModxApiTalk\Model\ModxApiTalkApiResult;

class Create extends CreateProcessor
{
    public $objectType = 'ModxApiTalkApiResult';
    public $classKey = ModxApiTalkApiResult::class;
    public $languageTopics = ['modxapitalk'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('result'));
        if (empty($name)) {
            $this->modx->error->addField('response', $this->modx->lexicon('modxapitalk_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, criteria: ['response' => $name])) {
            $this->modx->error->addField('response', $this->modx->lexicon('modxapitalk_item_err_ae'));
        }

        return parent::beforeSet();
    }
}
