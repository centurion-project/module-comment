<?php

class Comment_AdminWallController extends Centurion_Controller_CRUD
{

    protected $_formClassName = 'Comment_Form_Model_Wall';
    protected $_defaultOrder = 'created_at desc';

    public function sort($a, $b)
    {
        return strcmp(Centurion_Inflector::slugify($a), Centurion_Inflector::slugify($b));
    }
    public function preDispatch()
    {
        $this->_helper->authCheck('/admin/login');
        $this->_helper->aclCheck();

        $this->_helper->layout->setLayout('admin');

        parent::preDispatch();
    }

    public function getLinkFront($row)
    {
        return '<a href="' . $row->getProxy()->permalink. '">Show in front</a>';
    }

    public function init()
    {
        $this->view->noAddButton = true;
        $this->_canBeExport = true;

        $this->_displays = array (
            //'user_id' => $this->view->translate('user_id'),
            'by_profile__fullname' => array(
                'type' => self::COL_TYPE_FIRSTCOL,
                'param' => array(
                    'cover' => null,
                    'title' => 'by_user__username',
                    'subtitle' => 'by_user__email'),
                'label' => $this->view->translate('Par'),
                'sortable' => false),
            'text' => $this->view->translate('Content'),
            'created_at' => $this->view->translate('Created at'),
            'getLinkFront' => array(
                'label' => $this->view->translate('Show in front'),
                'type' => self::COLS_CALLBACK
            )
        );

        $this->_filters = array (
            'created_at' => array(
                'label' => $this->view->translate('Posté le'),
                'behavior'  => self::FILTER_BEHAVIOR_BETWEEN,
                'type'  => self::FILTER_TYPE_BETWEEN_DATETIME,
            ),
        );

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage wall'));
        $this->view->placeholder('headling_1_add_button')->set($this->view->translate('wall'));

        parent::init();

        unset($this->_toolbarActions['delete']);
        unset($this->_rowActions['Delete']);
    }

    public function _getHeaderForExport()
    {
        return array(
            $this->view->translate('Id'),
            $this->view->translate('Content'),
            $this->view->translate('Firstname'),
            $this->view->translate('Lastname'),
            $this->view->translate('Email'),
            $this->view->translate('Link to front'),
            $this->view->translate('Created at'),
        );
    }

    public function _getSelectForExport()
    {
        if (null === $this->_select) {
            $this->_getSelect();
            $this->_select->reset('columns');


            $this->_select->setIntegrityCheck(false);
            $firstName = $this->_select->addRelated('by_profile__firstname');
            $email = $this->_select->addRelated('by_user__email');

            $this->_select->columns('id');
            $this->_select->columns('text');
            $this->_select->columns(new Zend_Db_Expr($firstName));
            $this->_select->columns(new Zend_Db_Expr('user_profile.lastname'))
                ->columns(new Zend_Db_Expr($email))
                ->columns('created_at')
                ->order('created_at desc')
            ;
        }

        return $this->_select;
    }
    public function _getSelect()
    {
        if (null === $this->_select) {
            parent::_getSelect();
            $this->_select->filter(array('is_system' => 0));

            $this->_select->hydrate(array('by_profile' => array('id' , 'user_id')));

            $this->_select->columns(new Zend_Db_Expr($this->_select->addRelated('by_profile__left|avatar__id') . ' as by_profile___avatar___id'));
            $this->_select->columns(new Zend_Db_Expr('media_file.file_id as by_profile___avatar___file_id'));
            $this->_select->columns(new Zend_Db_Expr('media_file.filename as by_profile___avatar___filename'));
            $this->_select->columns(new Zend_Db_Expr('media_file.created_at as by_profile___avatar___created_at'));

            $this->_select->hydrate(array('by_user' => array('id', 'email', 'username')));
        }

        return $this->_select;
    }

}

