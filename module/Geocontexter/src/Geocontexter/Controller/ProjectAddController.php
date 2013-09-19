<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new project
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ProjectAddController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/project-add/index.phtml' );

        $this->view->id_project = $this->id_project = $this->params()->fromRoute('id_project',false);

        if ((false === $this->id_project) || (0 == $this->id_project)) {
            $this->id_project = 0;
            $this->renderer->headTitle('Add new project to parent root');
        }

        $this->view->id_project                 = $this->id_project;
        $this->view->project_branch_result      = array();
        $this->view->project_name               = '';
        $this->view->project_description        = '';
        $this->view->project_id_status          = 100;
        $this->view->project_lang               = 'en';
        $this->view->error                      = array();
    }

    public function indexAction()
    {
        $this->init_data();
        return $this->view;
    }

    private function init_data()
    {
        if (0 != $this->id_project) {
            // get current project as parent of the new project
            //
            $ProjectGet = $this->CoreModel('ProjectGet');
            $params  = array('id_project' => $this->id_project);
            $result  = $ProjectGet->run( $params );

            if ($result === false) {
                throw new \Exception ('Project id dosent exists: ' . $this->id_project);
            }

            $this->renderer->headTitle('Add new project to parent root ' . $result['title']);
        }

        $this->view->partialData = array('id_project'  => $this->id_project,
                                         'active_page' => 'add');

        // get project branch branch
        //
        $ProjectGetFromParentBranch = $this->CoreModel('ProjectGetFromParentBranch');

        $params = array('id_project' => $this->id_project) ;

        $this->view->project_branch_result = $ProjectGetFromParentBranch->run( $params );

        // get only languages that are flagged enabled=true
        //
        $LanguagesGet = $this->CoreModel('LanguagesGet');

        $params = array('enable' => 'true');

        $this->view->languages = $LanguagesGet->run( $params );
    }

    /**
     * add new project
     */
    public function addAction()
    {
        $this->id_project = $this->request->getPost()->id_project;

        if ($this->id_project === null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/project');
        }

        // cancel action ?
        //
        $cancel = $this->request->getPost()->cancel;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/project/index/id_project/' . $this->id_project);
        }

        // this array will contains data for the new project
        $params  = array();
        $_error = array();

        $this->view->project_name =
            $params['data']['title'] = $this->request->getPost()->project_name;

        $this->view->project_description =
            $params['data']['description'] = $this->request->getPost()->project_description;

        $params['data']['id_parent'] = $this->id_project;

        $this->view->project_id_status =
            $params['data']['id_status'] = $this->request->getPost()->project_id_status;

        $this->view->project_lang =
            $params['data']['lang'] = $this->request->getPost()->project_lang;

        if (empty($params['data']['title'])) {
            $_error[] = 'project name is empty';
        }

        if (count($_error) > 0) {
            $this->init_data();
            $this->view->error = $_error;
            $this->renderer->headTitle(implode('|', $_error));
            return $this->view;
        }

        // add project
        //
        $ProjectAdd = $this->CoreModel('ProjectAdd');

        $ProjectAdd->run( $params );

        return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/project/index/id_project/' . $this->id_project);
    }
}

