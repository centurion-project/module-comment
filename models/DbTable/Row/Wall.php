<?php

class Comment_Model_DbTable_Row_Wall extends Centurion_Db_Table_Row_Abstract
{
    protected $_proxy = null;

    public function init()
    {
        $this->_specialGets['proxy'] = 'getProxy';
        parent::init();
    }

    public function getProxy()
    {
        if (null === $this->_proxy && null !==  $this->proxy_id) {
            $proxyTable = Centurion_Db::getSingletonByClassName($this->proxy_model->name);
            $this->_proxy = $proxyTable->findOneById($this->proxy_id);
        }

        return $this->_proxy;
    }
}
