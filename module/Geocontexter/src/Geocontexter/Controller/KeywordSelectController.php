<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Select keyword
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class KeywordSelectController extends AbstractController
{
   public function init()
   {
        $this->initView( 'geocontexter/keyword-select/index.phtml' );

        $this->view->list_result      = array();
        $this->view->error = array();

        // -------------------------
        // get model callback
        // -------------------------
        $this->ModelCallback = $this->CoreModel('ModelCallback');
        $this->ModelCallback->session = $this->sessionGet();

        $this->callback_num = $this->params()->fromRoute('callback_num',false);

        if(false === $this->callback_num) {
            $this->callback_num = $this->request->getPost()->callback_num;
        }

        $this->model_info = $this->ModelCallback->get( $this->callback_num );

        if (false === $this->model_info) {
            return $this->error( 'callback number dosent exists: ' . var_export($this->callback_num,true), __file__, __line__ );
        }

        $this->view->opener_url          = $this->model_info['opener_url'];
        $this->view->callback_num        = $this->callback_num;
        $this->view->input_type          = $this->model_info['input_type'];

        $this->view->id_value = false;

        if (isset($this->model_info['check_circular'])) {
            $this->view->id_value = $this->model_info['id_value'];
        }

        // assign html head title
        //
        $this->renderer->headTitle('Select keywords ');
    }

    public function indexAction()
    {
        $KeywordGetChilds = $this->CoreModel('KeywordGetChilds');

        $params  = array('id_parent' => 0) ;

        $this->view->keyword_result = $KeywordGetChilds->run( $params );

        return $this->view;
    }

    /**
     *
     */
    public function submitAction()
    {

        $id_keyword = $this->request->getPost()->_id_keyword;

        if (null === $id_keyword) {
            return $this->indexAction();
        }

        $model = $this->CoreModel($this->model_info['model_class']);

        $params = array($this->model_info['id_name']     => $this->model_info['id_value'],
                        $this->model_info['model_field'] => $id_keyword);

        $methode = $this->model_info['model_class_methode'];

        $result = $model->$methode( $params );

        $this->view->close = true;

        return $this->indexAction();
    }


}

