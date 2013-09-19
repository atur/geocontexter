<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Main list controller
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ListController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/list/index.phtml' );

        $this->view->id_list = $this->id_list = $this->params()->fromRoute('id_list',false);

        if (false === $this->id_list) {
            $this->id_list = 0;
            $this->renderer->headTitle('Show child lists and items of Root');
        }

        $this->sessionOffsetSet('ListController_id_list', $this->id_list);

        $this->view->list_result                = array();
        $this->view->item_result                = array();
        $this->view->id_list                    = $this->id_list;
        $this->view->partialData = array('id_list'     => $this->id_list,
                                         'active_page' => 'main');
        $this->view->error                      = array();

    }

    public function indexAction()
    {
        $page = $this->params()->fromRoute('page',false);

        $itemCountPerPage = 30;

        // set offset for sql limit clause
        //
        if ((false === $page) || (1 == $page)) {
            $offset = 0;
            $page   = 1;
        } else {
            $offset = ($page - 1) * $itemCountPerPage;
        }

        if (0 != $this->id_list) {
            // get current list
            //
            $ListGet = $this->CoreModel('ListGet');
            $params  = array('id_list' => $this->id_list);
            $result  = $ListGet->run( $params );

            $this->renderer->headTitle('Show child lists and items of ' . $result['title']);
        }

        $ListGetFromParentBranch = $this->CoreModel('ListGetFromParentBranch');

        $params  = array('id_list' => $this->id_list) ;

        $this->view->list_branch_result = $ListGetFromParentBranch->run( $params );

        $ListGetChilds = $this->CoreModel('ListGetChilds');

        $params  = array('id_parent'       => $this->id_list,
                         'default_display' => true,
                         'system_serial'   => true);

        $this->view->list_result = $ListGetChilds->run( $params );

        $ItemGetListRelated = $this->CoreModel('ItemGetListRelated');

        $params  = array('id_list'            => $this->id_list,
                         'default_display'    => true,
                         'system_serial'      => true,
                         'limit'              => array($itemCountPerPage,$offset));

        $this->view->item_result = $ItemGetListRelated->run( $params );

        // get total num rows without limit
        //
        $total_num_rows = $ItemGetListRelated->totalNumRows();

        // build paginator result
        //
        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Null($total_num_rows));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($itemCountPerPage);
        $paginator->setPageRange(10);
        \Zend\Paginator\Paginator::setDefaultScrollingStyle('Sliding');
        \Zend\View\Helper\PaginationControl::setDefaultViewPartial(
            'paginator-slide'
        );

        //$paginator->setView($this->renderer);


        //this is paginator
        $this->view->paginator = $paginator;

        return $this->view;
    }

    /**
     * upload lists backup file for import
     */
    public function importAction()
    {
        $upload = $this->getServiceLocator()->get('CoreUploadForm');
        $upload->init('upload-form');

        $request = $this->getRequest();
        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $upload->form->setData($post);
            if ($upload->form->isValid()) {

                $data = $upload->form->getData();

                $ListImport = $this->CoreModel('ListImport');

                $ListImport->run(array('file' => $data['upload_file']));
            }
        }

        $this->indexAction();
    }
}

