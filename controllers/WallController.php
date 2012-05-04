<?php

class Comment_WallController extends Centurion_Controller_Action
{
    /**
     * @TODO: change it to option value
     */
    const MESSAGE_MAX = 10;

    public function getAllAction()
    {
        $messageId = $this->_getParam('id');
        $messageRow = Centurion_Db::getSingleton('comment/comment')->findOneById($messageId);

        $this->view->message = $messageRow;
        $this->view->full = true;
        $this->view->messageMax = self::MESSAGE_MAX;

        $this->getHelper('layout')->disableLayout();

        $this->render('messages');
    }

    protected function _getForm($row)
    {
        $id = md5(get_class($row) . '-' . $row->id);

        $form = new Comment_Form_Model_Wall(array('formId' => $id));
        $form->cleanForm();
        $form->setMethod('post');
        //TODO: EURK !!!

        $form->setAttrib('class', 'msg-form');
        $form->getElement('text')->setAttrib('noLabel', true);
        $form->getElement('text')->setAttrib('rows', '15');
        $form->getElement('text')->setAttrib('cols', '65');
        $form->getElement('text')->setAttrib('placeholder', $this->view->translate('Laissez un message'));

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel($this->view->translate('Publish'));

        $submit->clearDecorators();

        $submit->setAttrib('class', 'btn btn-gray');
        $submit->setAttrib('type', 'submit');

        $submit->addDecorator('Texte', array('escape' => 'false', 'label' => $submit->getLabel()));

        $submit->addDecorator(array('span' => 'HtmlTag'), array('tag' => 'span ', 'class' => 'text'));
        $submit->addDecorator('Button');
        $submit->addDecorator(
            array('div' => 'HtmlTag'),
            array('tag' => 'div', 'class' => 'form-submit')
        );

        $form->addElement($submit);

        $form->removeElement('_XSRF');

        $form->setAction($this->view->url());

        return $form;
    }

    /**
     *
     * This action can not be called by GET function.
     * You must dispatch here manually ($this->_redirect or $this->action)
     *
     *
     */
    public function getAction()
    {
        $this->_helper->authCheck();

        $proxyRow = $this->_getParam('proxy', null);

        if (null === $proxyRow || !($proxyRow instanceof Centurion_Db_Table_Row_Abstract)) {
            throw new Centurion_Controller_Action_Exception('The proxy is not a valid one');
        }

        $form = $this->_getForm($proxyRow);
        $proxyContentTypeId = Centurion_Db::getSingleton('core/contentType')->getContentTypeIdOf($proxyRow);

        $this->view->form = clone $form;

        if ($form->hasBeenPost($this->getRequest())) {
            if ($form->isValid($this->getRequest()->getParams())) {
                $values = $form->getValues();
                $values['by_user_id'] = $this->getUser()->getIdentity()->id;
                $values['proxy_model_id'] = $proxyContentTypeId;
                $values['proxy_id'] = $proxyRow->id;
                $values['language_id'] = null;
                $values['is_info'] = 0;

                if (!isset($values['response_to_id'])) {
                    $values['response_to_id'] = null;
                }

                $form->setValues($values);
                $instance = $form->save();

                $instance->last_response_at = new Zend_Db_Expr('Now()');
                $instance->save();

                if (null !== $instance->response_to) {
                    //We force save on parent to clear cache.
                    $instance->response_to->last_response_at = new Zend_Db_Expr('Now()');
                    $instance->response_to->save();
                }

                $instance->getProxy()->save();

                $this->_redirect($this->view->url());
                return;
            } else {
                $form->populate(array());
            }
        }

        Centurion_Cache_TagManager::addTag(Centurion_Db::getSingleton('comment/wall'));

        $select = $this->_getSelect($proxyRow, $proxyContentTypeId);

        $this->view->messageMax = self::MESSAGE_MAX;
        $this->view->messages = $this->_getPaginator($select);

        $this->view->proxyId = $proxyContentTypeId;
        $this->view->id = $proxyRow->id;
    }

    protected function _getPaginator($select)
    {
        $paginator = new Zend_Paginator(new Centurion_Paginator_Adapter_DbTable($select));
        $paginator->setItemCountPerPage(self::MESSAGE_MAX);
        $paginator->setCurrentPageNumber($this->_getParam('page', 0));

        return $paginator;
    }

    protected function _getSelect($proxyRow, $proxyContenteTypeId)
    {
        $select =  Centurion_Db::getSingleton('comment/wall')->select(true)
            ->filter(array('proxy_model_id' => $proxyContenteTypeId, 'proxy_id' => $proxyRow->id, 'response_to_id__isnull' => null))
            ->setIntegrityCheck(false)
            ->order('last_response_at desc');

        //TODO: use bind

        //This is done to order messages by last responsed
        /*$select->joinLeft(new Zend_Db_Expr('(SELECT max(created_at) as created_at_2, response_to_id
            FROM `comment_wall` as `comment_wall_2`
            WHERE `response_to_id` is not null
            and proxy_id = '.$proxyRow->id.' and proxy_model_id = '.$proxyContenteTypeId.'
            group by response_to_id
            )'), 't.response_to_id = comment_wall.id', null);

        $select->group('comment_wall.id');*/

        //$select->hydrate(array('by_user' => array('username', 'email')));
        return $select;
    }

    public function moreAction()
    {
        $this->getHelper('layout')->disableLayout();

        //TODO: protect it
        $contentType = Centurion_Db::getSingleton('core/contentType')->findOneById($this->_getParam('proxyId'));
        $proxyRow = Centurion_Db::getSingletonByClassName($contentType->name)->findOneById($this->_getParam('id'));

        $form = $this->_getForm($proxyRow);

        $proxyContenteTypeId = Centurion_Db::getSingleton('core/contentType')->getContentTypeIdOf($proxyRow);

        $select = $this->_getSelect($proxyRow, $proxyContenteTypeId);

        $params = array();
        $params['messageMax'] = self::MESSAGE_MAX;
        $params['messages'] = $this->_getPaginator($select);

        $params['form'] = $form;

        $params['proxyId'] = $this->_getParam('proxyId');
        $params['id'] = $this->_getParam('id');

        $result = array();
        $result['messages'] = $this->renderToString('wall/messagesli.phtml', $params);
        $result['more'] = $this->renderToString('wall/more.phtml', $params);

        $this->_helper->json($result, true);
        die();
    }
}
