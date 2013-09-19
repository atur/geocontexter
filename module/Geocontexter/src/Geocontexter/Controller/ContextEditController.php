<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Update context
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ContextEditController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/context-edit/index.phtml' );

        $this->view->context_id_parent = $this->id_context = $this->params()->fromRoute('id_context',false);

        if ((null === $this->id_context) || (0 == $this->id_context)) {
            return $this->error( '0 or no id_context request parameter defined.', __file__, __line__ );
        }

        // check if there are records with relations to this context
        //
        $hasRecord = $this->CoreModel('RecordHasRelation');

        $params  = array('id_name' => 'id_context',
                         'id'      => $this->id_context);

        $result  = $hasRecord->run( $params );

        if (true === $result) {
            $this->view->has_record_relation = true;
        }

        // init some view vars
        //
        $this->view->id_context                 = $this->id_context;
        $this->view->context_branch_result      = array();
        $this->view->context_name               = '';
        $this->view->context_description        = '';
        $this->view->context_id_status          = 100;
        $this->view->context_lang               = 'en';
        $this->view->error                      = array();

        // partial view variables for layout/context_navigation.phtml
        //
        $this->view->partialData = array('active_page' => '',
                                         'id_context'  => $this->id_context);
    }

    public function indexAction()
    {
        if (isset($this->error_view)) {
            return $this->error_view;
        }

        $this->fetch_context();
        $this->fetch_data();

        $this->renderer->headTitle('Edit context ' . $this->view->context_result['title']);

        return $this->view;
    }

    /**
     * Update context
     */
    public function updateAction()
    {
        $this->id_context = $this->request->getPost()->id_context;

        $this->fetch_context();
        $this->fetch_data();

        $cancel = $this->request->getPost()->cancel;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/context/index/id_context/' . $this->request->getPost()->id_parent);
        }

        $params  = array();

        $context_update = $this->CoreModel('ContextUpdate');

        $params['title']       = $this->request->getPost()->context_name;
        $params['description'] = $this->request->getPost()->context_description;
        $params['id_status']   = $this->request->getPost()->id_status;
        $params['lang']        = $this->request->getPost()->lang;
        $params['id_parent']   = $this->request->getPost()->id_parent;

        // if status is trash then check if there is no related project
        //
        if($params['id_status'] == 0)
        {
            $context_related_project_check = $this->CoreModel('ContextRelatedProjectCheck');
            $__params = array('id_context' => $this->id_context);
            $result = $context_related_project_check->run( $__params );

            if (false === $result) {
                $message = 'Couldnt move this context to trash. There are some projects related to this context or to its child contexts. Please delete or edit first the projects.';
                $this->view->error = array($message);
                $this->renderer->headTitle($message);
                return $this->view;
            }
        }

        if (empty($params['title'])) {
            $this->view->error = array('Context name is empty');
            $this->renderer->headTitle('Error: Context name field is empty');
            $this->view->context_result = $params;
            return $this->view;
        }

        $this->view->context_result = $params;

        // update context
        //
        $data =  array('id_context' => $this->id_context,
                       'data'       => $params );

        $result = $context_update->run($data);

        return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/context/index/id_context/' . $this->view->context_result['id_parent']);
    }

    private function fetch_context()
    {
        $context = $this->CoreModel('ContextGet');

        $params         = array('id_context' => $this->id_context);
        
        $result_context = $context->run( $params );

        $this->view->context_result = $result_context;
        $this->renderer->headTitle('Edit context ' . $result_context['title'], 'PREPEND');
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

        $params  = array('id_context'         => 0,
                         'exclude_id_context' => $this->id_context) ;

        $this->view->context_tree_result = $context_tree->run( $params );

        // languages
        //
        $languages = $this->CoreModel('LanguagesGet');

        $params = array('enable' => 'true');

        $this->view->languages = $languages->run( $params );
    }
}

