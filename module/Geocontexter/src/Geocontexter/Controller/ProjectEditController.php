<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Edit project
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 857 $ / $LastChangedDate: 2011-04-17 10:15:13 +0200 (So, 17 Apr 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ProjectEditController extends AbstractController
{
   public function init()
    {
        $this->initView( 'geocontexter/project-edit/index.phtml' );

        $this->view->id_project = $this->id_project = $this->params()->fromRoute('id_project',false);

        if ((false === $this->id_project) || (0 == $this->id_project)) {
            return $this->error( '0 or no id_project request parameter defined.', __file__, __line__ );
        }

        $this->view->project_branch_result      = array();
        $this->view->attribute_groups           = array();
        $this->view->lists_result               = array();
        $this->view->project_result             = array();
        $this->view->keywords_result            = array();
        $this->view->result_context             = array();
        $this->view->result_controllers         = array();
        $this->view->users_result               = array();
        $this->view->project_name               = '';
        $this->view->project_description        = '';
        $this->view->project_start_date         = '';
        $this->view->project_end_date           = '';
        $this->view->project_id_status          = 100;
        $this->view->project_lang               = 'en';
        $this->view->error                      = array();
    }

    public function indexAction()
    {
        $this->init_data();
        return $this->view;
    }

    /**
     * update project
     */
    public function updateAction()
    {
        // check on cancel action
        //
        $cancel    = $this->request->getPost()->cancel;
        $id_parent = $this->request->getPost()->_id_parent;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/project/index/id_project/' . $id_parent);
        }

        $params  = array();

        $params['id_project'] = $this->id_project = $this->request->getPost()->id_project;

        $remove_project_list = $this->request->getPost()->delete_id_list;

        if (($remove_project_list !== null) && is_array($remove_project_list)) {
            $params['remove_project_list'] = $remove_project_list;
        }

        $remove_project_user = $this->request->getPost()->delete_id_user;
        if (($remove_project_user !== null) && is_array($remove_project_user)) {
            $params['remove_project_user'] = $remove_project_user;
        }

        $params['data']['title'] = $this->request->getPost('project_title');

        $params['data']['description'] = $this->request->getPost()->project_description;

        $params['data']['date_project_start'] = $this->request->getPost()->date_project_start;

        $params['data']['date_project_end'] = $this->request->getPost()->date_project_end;

        $params['data']['id_status'] = $this->request->getPost()->project_id_status;

        $params['data']['controller'] = trim($this->request->getPost()->project_controller);

        $params['data']['lang'] = $this->request->getPost()->project_lang;

        $delete_id_keyword = $this->request->getPost()->delete_id_keyword;

        if (($delete_id_keyword) !== null && (count($delete_id_keyword) > 0)) {
            $params['remove_id_keyword'] = $delete_id_keyword;
        }

        if (empty($params['data']['title'])) {
            $this->view->error[] = 'Project title is empty';
            $this->view->headTitle('Error: Project title field is empty', 'PREPEND');
        }

        if (count($this->view->error) > 0) {
            $this->view->project_result = $params['data'];
            $this->init_data();
            return $this->view;
        }

        // update project
        //
        $ProjectUpdate = $this->CoreModel('ProjectUpdate');

        $ProjectUpdate->run( $params );

        return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/project/index/id_project/' . $id_parent);
    }

    /**
     * Register of model callback classes
     *
     *
     *
     * @param object $session
     */
    private function register_model_callbacks()
    {
        $this->ModelCallback = $this->CoreModel('ModelCallback');
        $this->ModelCallback->session = $this->sessionGet();

        // url to reload after a model callback was done
        //
        $this->opener_url = $this->getAdminBaseUrl() . '/project-edit/index/id_project/'.$this->id_project;

        //
        // add project lists
        //
        $params_list =
                  array('model_class'         => 'ProjectAddList',
                        'model_class_methode' => 'run',
                        'model_field'         => 'id_list',
                        'id_name'             => 'id_project',
                        'id_value'            => $this->id_project,
                        'input_type'          => 'checkbox',
                        'opener_url'          => $this->opener_url);

        $callback_number  = $this->ModelCallback->register( $params_list );

        $this->view->project_lists_callback_number = $callback_number;

        //
        // add/update project context
        //
        $params_context =
                  array('model_class'         => 'ProjectUpdateParent',
                        'model_class_methode' => 'run',
                        'model_field'         => 'id_parent',
                        'check_circular'      => true,
                        'id_name'             => 'id_project',
                        'id_value'            => $this->id_project,
                        'input_type'          => 'radio',
                        'opener_url'          => $this->opener_url);

        $callback_number  = $this->ModelCallback->register( $params_context );

        $this->view->project_parent_callback_number = $callback_number;

        //
        // add/update project context
        //
        $params_context =
                  array('model_class'         => 'ProjectUpdate',
                        'model_class_methode' => 'addContext',
                        'model_field'         => 'id_context',
                        'id_name'             => 'id_project',
                        'id_value'            => $this->id_project,
                        'input_type'          => 'radio',
                        'opener_url'          => $this->opener_url);

        $callback_number  = $this->ModelCallback->register( $params_context );

        $this->view->project_context_callback_number = $callback_number;

        //
        // project_keywords
        //
        $params_project_keyword =
                  array('model_class'         => 'ProjectAddKeyword',
                        'model_class_methode' => 'run',
                        'model_field'         => 'id_keyword',
                        'id_name'             => 'id_project',
                        'id_value'            => $this->id_project,
                        'input_type'          => 'checkbox',
                        'opener_url'          => $this->opener_url);

        $callback_number  = $this->ModelCallback->register( $params_project_keyword );

        $this->view->project_keyword_callback_number = $callback_number;
    }

    public function init_data()
    {
        // init of model callbacks. used for new window model calls.
        //
        $this->register_model_callbacks();

        $ProjectGet = $this->CoreModel('ProjectGet');

        $params  = array('id_project'    => $this->id_project,
                         'system_serial' => true);

        $project_result  = $ProjectGet->run( $params );

        if ($project_result === false) {
            throw new \Exception ('Project id dosent exists: ' . $this->id_project);
        }

        $this->view->project_result = $project_result;

        // assign html head title
        //
        $this->renderer->headTitle('Edit project ' . $project_result['title'], 'PREPEND');

        $this->view->partialData = array('id_project'  => $project_result['id_parent'],
                                         'active_page' => 'edit_item');

        $ProjectGetFromParentBranch = $this->CoreModel('ProjectGetFromParentBranch');

        $params  = array('id_project' => $this->id_project) ;

        $result_branch  = $ProjectGetFromParentBranch->run( $params );

        $this->view->project_branch_result = $result_branch;

        $ListGetProjectRelated = $this->CoreModel('ListGetProjectRelated');

        $params  = array('id_project'    => $this->id_project,
                         'system_serial' => true);

        $result  = $ListGetProjectRelated->run( $params );

        $this->view->lists_result = $result;

        $UserGetProjectRelated = $this->CoreModel('UserGetProjectRelated');

        $params  = array('id_project'    => $this->id_project,
                         'system_serial' => true);

        $result_users  = $UserGetProjectRelated->run( $params );

        $this->view->users_result = $result_users;

        $ProjectGetPublicProjectFoldersControllers = $this->CoreModel('ProjectGetPublicProjectFoldersControllers');

        $result_controllers  = $ProjectGetPublicProjectFoldersControllers->run( $params );

        $this->view->result_controllers = $result_controllers;

        // get availaible languages
        //
        $LanguagesGet = $this->CoreModel('LanguagesGet');

        // optional
        $params = array('enable' => 'true');

        $lang_result  = $LanguagesGet->run( $params );

        $this->view->languages = $lang_result;

        // get project related keywords
        //
        $ProjectGetKeywordBranches = $this->CoreModel('ProjectGetKeywordBranches');

        $params  = array('id_project' => $this->id_project);

        $result_key_branches  = $ProjectGetKeywordBranches->run( $params );

        $this->view->keywords_result = $result_key_branches;

        if (!empty($project_result['id_context'])) {

           $ContextGetBranch = $this->CoreModel('ContextGetBranch');

           $params  = array('id_context' => $project_result['id_context']);

           $result_context = $ContextGetBranch->run( $params );

           $this->view->result_context = $result_context;
        }
    }
}

