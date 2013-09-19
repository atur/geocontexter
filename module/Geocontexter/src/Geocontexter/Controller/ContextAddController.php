<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new context controller
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ContextAddController extends AbstractController
{
   public function init()
    {
        $this->initView( 'geocontexter/context-add/index.phtml' );

        $this->view->context_id_parent = $this->id_context = $this->params()->fromRoute('id_context',false);

        $this->view->id_context                 = $this->id_context;
        $this->view->context_branch_result      = array();
        $this->view->context_name               = '';
        $this->view->context_description        = '';
        $this->view->context_id_status          = 100;
        $this->view->context_lang               = 'en';
        $this->view->error                      = array();

        // partial view variables for layout/context_navigation.phtml
        //
        $this->view->partialData = array('active_page' => 'add',
                                         'id_context'  => $this->id_context);
    }

    public function indexAction()
    {
        if ((false === $this->id_context) || (0 == $this->id_context)) {
            $this->id_context = 0;
            $this->view->context_id_context = $this->view->context_id_parent = 0;
            $this->renderer->headTitle('Add new context to parent Root');
        }
        
        $result_context = $this->fetch_context();

        if ($result_context === false) {
            return $this->error( 'Context id doesent exists: ' . $this->id_context, __file__, __line__);
        }

        $result_data = $this->fetch_data();

        if ($result_data instanceof \Core\Library\Exception) {
            return $this->error( $result_data->getMessage(), __file__, __line__);
        }

        return $this->view;
    }

    /**
     * add new group action
     */
    public function addAction()
    {
        $this->context_id_parent = $this->request->getPost()->context_id_parent;
        $this->id_context = $this->context_id_parent;

        $cancel = $this->request->getPost()->cancel;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/context/index/id_context/' . $this->context_id_parent);
        }

        $result = $this->fetch_context();

        $this->renderer->headTitle('Add new context to parent ' . $result['title']);

        $this->fetch_data();

        $params  = array();

        $this->view->context_name =
            $params['title'] = $this->request->getPost()->context_name;

        $this->view->context_description =
            $params['description'] = $this->request->getPost()->context_description;

        $this->view->context_id_parent =
            $params['id_parent'] = $this->request->getPost()->context_id_parent;

        $this->view->context_id_status =
            $params['id_status'] = $this->request->getPost()->context_id_status;

        $this->view->context_lang =
            $params['lang'] = $this->request->getPost()->context_lang;

        if (empty($params['title'])) {
            $this->view->error = array('Context name is empty');
            $this->renderer->headTitle('Error: Context name field is empty');
            return $this->view;
        }

        $context_add = $this->CoreModel('ContextAdd');
        
        $context_add->run( $params );

        return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/context/index/id_context/' . $this->request->getPost()->context_id_parent);
    }

    private function fetch_context()
    {
        if (0 != $this->id_context) {
            // get parent context
            //
            $context = $this->CoreModel('ContextGet');

            $params = array('id_context' => $this->id_context);

            $result_context = $context->run( $params );

            if ($result_context !== false) {
              $this->view->context_result = $result_context;
            }

            return $result_context;
        }

        return true;
    }

    private function fetch_data()
    {
        // get branch of the current context
        //
        $context_branch = $this->CoreModel('ContextGetFromParentBranch');

        $params  = array('id_context' => $this->id_context) ;

        $this->view->context_branch_result = $context_branch->run( $params );

        // get the whole context tree from root except of the current context
        //
        $context_tree = $this->CoreModel('ContextGetTree');

        $params  = array('id_context' => 0) ;

        $this->view->context_tree_result = $context_tree->run( $params );

        // languages
        //
        $languages = $this->CoreModel('LanguagesGet');

        $params = array('enable' => 'true');

        $this->view->languages = $languages->run( $params );
    }
}

