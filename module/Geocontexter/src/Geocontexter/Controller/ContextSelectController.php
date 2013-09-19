<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Select context
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

class Geocontexter_ContextSelectController extends Mozend_Controller_Action_AbstractAdmin
{
   public function preDispatch()
    {
        // set view. we keep allways the index view
        //
        $this->_helper->viewRenderer->setScriptAction('index');

        $this->view->error       = array();

        $session = Zend_Registry::get('session');

        // -------------------------
        // get model callback
        // -------------------------
        $this->model_callback = new Geocontexter_Model_ModelCallback( $session );

        $this->callback_num = $this->request->getParam('callback_num');

        $this->model_info = $this->model_callback->get( $this->callback_num );

        if(false === $this->model_info)
        {
            return $this->error( 'callback number dosent exists: ' . var_export($this->callback_num,true), __file__, __line__ );
        }

        $this->view->opener_url          = $this->model_info['opener_url'];
        $this->view->callback_num        = $this->callback_num;
        $this->view->input_type          = $this->model_info['input_type'];

        $this->view->id_value = false;

        if(isset($this->model_info['check_circular']))
        {
            $this->view->id_value = $this->model_info['id_value'];
        }


        // assign html head title
        //
        $this->view->headTitle('Select context ', 'PREPEND');
    }

    public function indexAction()
    {
        $context = new Geocontexter_Model_ContextGetChilds;

        $params  = array('id_parent' => 0) ;

        $result  = $context->get( $params );

        if($result instanceof Mozend_ModelError)
        {
           return $this->error( $result->getErrorString(), __file__, __line__ );
        }
        else
        {
           $this->view->context_result = $result;
        }
    }

    /**
     *
     */
    public function submitAction()
    {
        $id_context = $this->request->getParam('_id_context');

        if(null === $id_context)
        {
            $this->indexAction();
            return;
        }

        $model = new $this->model_info['model_class'];

        $params = array($this->model_info['id_name']     => $this->model_info['id_value'],
                        $this->model_info['model_field'] => $id_context);

        $methode = $this->model_info['model_class_methode'];
        $result = $model->$methode( $params );

        if($result instanceof Mozend_ModelError)
        {
           return $this->error( $result->getErrorString(), __file__, __line__ );
        }
        else
        {
            $this->view->close = true;
        }

        $this->indexAction();
    }


}

