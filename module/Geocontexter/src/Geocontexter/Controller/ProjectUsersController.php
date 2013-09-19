<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Manage project users
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 844 $ / $LastChangedDate: 2011-03-24 09:50:17 +0100 (Do, 24 Mrz 2011) $ / $Author: armand.turpel $
 */

class Geocontexter_ProjectUsersController extends Mozend_Controller_Action_AbstractAdmin
{
    public function preDispatch()
    {
        $this->view->project_result = array();
        $this->view->users_result   = array();

        // set view. we keep the index view instead of add
        //
        $this->_helper->viewRenderer->setScriptAction('index');

        $this->id_project = $this->request->getParam('id_project');

        if((null === $this->id_project) || (0 == $this->id_project))
        {
            return $this->error( '0 or no id_project request parameter defined.', __file__, __line__ );
        }

        $this->view->id_project                 = $this->id_project;
        $this->view->partialData['active_page'] = 'project_users';
        $this->view->partialData['id_project']  = $this->id_project;
        $this->view->error                      = array();

        $session = Zend_Registry::get('session');

        $this->register_model_callbacks( $session );
    }


    public function indexAction()
    {
        $this->init_data();
    }

    /**
     * update project
     */
    public function updateAction()
    {
        // check on cancel action
        //
        $cancel    = $this->request->getPost('cancel');

        if($cancel !== null)
        {
            $this->_redirect($this->view->adminAreaToken .
                             '/geocontexter/project-edit/index/id_project/' . $this->id_project);
        }

        $params  = array();

        $this->view->error = array();

        $params['id_project'] = $this->id_project;

        $remove_project_user = $this->request->getPost('delete_id_user');

        if(count($this->view->error) > 0)
        {
            $this->init_data();
            return;
        }

        // remove project users
        //
        if(($remove_project_user !== null) && is_array($remove_project_user))
        {
            $project = new Geocontexter_Model_ProjectRemoveUsers;
            $params['id_user'] = $remove_project_user;
            $result = $project->remove( $params );
        }

        if($result instanceof Mozend_ModelError)
        {
           return $this->error( $result->getErrorString(), __file__, __line__ );
        }
        else
        {
            $this->_redirect($this->view->adminAreaToken .
                             '/geocontexter/project-edit/index/id_project/' . $this->id_project);
        }
    }

    public function init_data()
    {
        // get current project
        //
        $project         = new Geocontexter_Model_ProjectGet;
        $params          = array('id_project' => $this->id_project);

        $result_project  = $project->get( $params );

        if($result_project instanceof Mozend_ModelError)
        {
           return $this->error( $result_project->getErrorString(), __file__, __line__ );
        }
        else
        {
           $this->view->headTitle('Users of project ' . $result_project['title'], 'PREPEND');
           $this->view->project_result = $result_project;
        }

        $users         = new Geocontexter_Model_UserGetProjectRelated;
        $params        = array('id_project'    => $this->id_project,
                               'system_serial' => true);

        $result_users  = $users->get( $params );

        if($result_users instanceof Mozend_ModelError)
        {
           return $this->error( $result_users->getErrorString(), __file__, __line__ );
        }
        else
        {
           $this->view->users_result = $result_users;
        }
    }

    /**
     * Register of model callback classes
     *
     *
     *
     * @param object $session
     */
    private function register_model_callbacks( & $session )
    {
        $action_callback = new Geocontexter_Model_ModelCallback( $session );

        // url to reload after a model callback was done
        //
        $this->opener_url = $this->view->baseUrl().'/'.$this->view->adminAreaToken.'/geocontexter/project-users/index/id_project/'.$this->id_project;

        //
        // add project users
        //
        $params_users =
                  array('model_class'         => 'Geocontexter_Model_ProjectAddUsers',
                        'model_class_methode' => 'add',
                        'model_field'         => 'id_user',
                        'id_name'             => 'id_project',
                        'id_value'            => $this->id_project,
                        'input_type'          => 'checkbox',
                        'opener_url'          => $this->opener_url);

        $callback_number  = $action_callback->register( $params_users );

        if($callback_number instanceof Mozend_ModelError)
        {
           return $this->error( $callback_number->getErrorString(), __file__, __line__ );
        }

        $this->view->project_users_callback_number = $callback_number;
    }

}

