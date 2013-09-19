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

class ItemSearchController extends AbstractController
{
   public function init()
   {

        $layout = $this->layout();
        $layout->setTemplate('layout/searchlayout.phtml');

        $this->initView( 'geocontexter/item-search/index.phtml' );


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
        $this->renderer->headTitle('Search item ');
    }

    public function indexAction()
    {
        return $this->view;
    }

    /**
     *
     */
    public function searchAction()
    {
        $this->view->callback_num = $this->params()->fromRoute('callback_num',false);

        $type = $this->params()->fromRoute('type',false);
        if (false === $type) {
            $type = $this->request->getPost()->type;
            if (null === $type) {
                $type = 'radio';
            }
        }
        $this->view->input_type = 'radio';
        $this->view->search_select = true;

        $page = $this->params()->fromRoute('page',false);

        $itemCountPerPage = 5;

        // set offset for sql limit clause
        //
        if ((false === $page) || (1 == $page)) {
            $offset = 0;
            $page   = 1;
        } else {
            $offset = ($page - 1) * $itemCountPerPage;
        }

        $item_search_string = $this->request->getPost()->item_search_string;

        if (null === $item_search_string) {

            $item_search_string = $this->params()->fromRoute('item',false);

            if ($item_search_string === false) {
                $this->view->item_search_string = '';
                return $this->indexAction();
            }
        }

        $this->view->item_search_string = $item_search_string;

        $ItemSearch = $this->CoreModel('ItemSearch');

        $params  = array('search'          => $item_search_string,
                         'default_display' => true,
                         'system_serial'   => true,
                         'limit'           => array($itemCountPerPage, $offset));

        $result = $ItemSearch->run( $params );

        $totalNumRows = $ItemSearch->totalNumRows();
        $this->view->num_rows = $totalNumRows;

        $ListGetItemRelated = $this->CoreModel('ListGetItemRelated');

        foreach ($result as & $item) {

            $_params  = array('id_item'                 => $item['id_item'],
                              'system_serial'           => true,
                              'order_by_preferred_list' => true,
                              'preferred_list'          => true);

            $_result  = $ListGetItemRelated->run( $_params );

            $item['lists'] = $_result;
        }

        $this->view->result = $result;

        // build paginator result
        //
        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Null($totalNumRows));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($itemCountPerPage);
        $paginator->setPageRange(10);
        \Zend\Paginator\Paginator::setDefaultScrollingStyle('Sliding');
        \Zend\View\Helper\PaginationControl::setDefaultViewPartial(
            'paginator-slide'
        );

        //this is paginator
        $this->view->paginator = $paginator;

        return $this->view;
    }

    /**
     *
     */
    public function submitAction()
    {

        $id_item = $this->request->getPost()->_id;

        if (null === $id_item) {
            return $this->indexAction();
        }

        $model = $this->CoreModel($this->model_info['model_class']);

        $params = array($this->model_info['id_name']     => $this->model_info['id_value'],
                        $this->model_info['model_field'] => $id_item);

        $methode = $this->model_info['model_class_methode'];

        $result = $model->$methode( $params );

        $this->view->close = true;

        return $this->indexAction();
    }


}

