<?php

class Comment_Model_DbTable_Wall extends Centurion_Db_Table_Abstract
{

    /**
     * The table name
     *
     * @var string
     */
    protected $_name = 'comment_wall';

    /**
     * The primary key column or columns
     *
     * @var mixed
     */
    protected $_primary = array('id');

    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Comment_Model_DbTable_Row_Wall';

    /**
     * Associative array map of declarative referential integrity rules.
     *
     * @var array
     */
    protected $_referenceMap = array(
        'response_to' => array(
            'columns' => 'response_to_id',
            'refColumns' => 'id',
            'refTableClass' => 'Comment_Model_DbTable_Wall'
        ),
        'by_user' => array(
            'columns' => 'by_user_id',
            'refColumns' => 'id',
            'refTableClass' => 'Auth_Model_DbTable_User'
        ),
        'by_profile' => array(
            'columns' => 'by_user_id',
            'refColumns' => 'user_id',
            'refTableClass' => 'User_Model_DbTable_Profile'
        ),
        'proxy_model' => array(
            'columns' => 'proxy_model_id',
            'refColumns' => 'id',
            'refTableClass' => 'Core_Model_DbTable_ContentType'
        )
    );

    protected $_meta = array(
        'verboseName' => 'wall',
        'verbosePlural' => 'walls'
    );

    /**
     * Simple array of class names of tables that are "children" of the current table.
     *
     * @var array
     */
    protected $_dependentTables = array('walls' => 'Comment_Model_DbTable_Wall');

    public function insertMessage($message, $proxy, $isSystem = 1, $byUserId = null)
    {
        $row = $this->createRow();

        if ($byUserId instanceof Centurion_Db_Table_Row_Abstract) {
            $byUserId = $byUserId->id;
        }

        $row->by_user_id = $byUserId;
        $row->proxy_model_id = Centurion_Db::getSingleton('core/contentType')->getContentTypeIdOf($proxy);
        $row->proxy_id = $proxy->id;
        $row->text = $message;
        $row->response_to_id = null;
        $row->is_system = $isSystem;

        $row->save();
    }
}


