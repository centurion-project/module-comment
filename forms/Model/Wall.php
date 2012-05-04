<?php

class Comment_Form_Model_Wall extends Centurion_Form_Model_Abstract
{
    protected $_modelClassName = 'Comment_Model_DbTable_Wall';

    public function __construct($options = array (), Centurion_Db_Table_Row_Abstract $instance = null)
    {
        $this->_exclude = array('id', 'created_at', 'updated_at');

        $this->_elementLabels = array(
            'text' => $this->_translate('Content'),
        );

        parent::__construct($options, $instance);
    }

    public function init()
    {
        $this->getElement('text')->setAttrib('rows', 2);
        $this->addElement('hidden', 'response_to_id', array());

        parent::init();
    }
}

