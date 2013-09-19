<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * keyword search controller
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

class Geocontexter_KeywordSearchController extends Mozend_Controller_Action_AbstractAdmin
{
    public function preDispatch()
    {
        // set view. we keep the index view instead of add
        //
        $this->_helper->viewRenderer->setScriptAction('index');

        $this->view->headTitle('Search for keywords' , 'PREPEND');

        $this->view->list_result = array();
        $this->view->error       = array();
    }

    public function indexAction()
    {
        $this->view->search = $search = trim($this->request->getParam('value'));
        $this->view->type   = $type   = $this->request->getParam('type');
        $page                         = $this->request->getParam('page');

        $itemCountPerPage = 5;

        if(null === $page)
        {
            $page = 0;
        }

        $keyword = new Geocontexter_Model_KeywordSearch;

        $all_result = $keyword->countAll(array('search' => $search));

        $params  = array('search'             => $search,
                         'default_display'    => true,
                         'system_serial'      => true,
                         'limit'              => array($itemCountPerPage,$page));

        $result  = $keyword->get( $params );

        if($result instanceof Mozend_ModelError)
        {
           return $this->error( $result->getErrorString(), __file__, __line__ );
        }
        else
        {
           $this->view->result = $result;
        }

        $paginator = Zend_Paginator::factory($all_result);

        $fO = array('lifetime' => 3600, 'automatic_serialization' => true);
        $bO = array('cache_dir'=> APPLICATION_PATH . '/modules/geocontexter/tmp');
        $cache = Zend_Cache::factory('Core', 'File', $fO, $bO);
        Zend_Paginator::setCache($cache);

        $paginator->setItemCountPerPage($itemCountPerPage);
        $paginator->setCurrentPageNumber($page);
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('_pagination_control.phtml');

        //this is paginator
        $this->view->paginator = $paginator;
    }
}

