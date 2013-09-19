<?php


namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ProjectController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/project/index.phtml' );

        $this->view->id_project = $this->id_project = $this->params()->fromRoute('id_project',false);

        if ((false === $this->id_project) || (0 == $this->id_project)) {
            $this->id_project = 0;
            $this->renderer->headTitle('Show child projects of Root');
        } else {
            // get current list
            //
            $ProjectGet = $this->CoreModel('ProjectGet');
            $params  = array('id_project' => $this->id_project);
            $result  = $ProjectGet->run( $params );

            $this->renderer->headTitle('Show child projects of ' . $result['title']);
        }

        $this->view->project_result             = array();
        $this->view->id_project                 = $this->id_project;

        $this->view->partialData = array('id_project'  => $this->id_project,
                                         'active_page' => 'main');
        $this->view->error = array();

    }

    public function indexAction()
    {
        $ProjectGetFromParentBranch = $this->CoreModel('ProjectGetFromParentBranch');

        $params  = array('id_project' => $this->id_project) ;

        $this->view->project_branch_result = $ProjectGetFromParentBranch->run( $params );

        $ProjectGetChilds = $this->CoreModel('ProjectGetChilds');

        $params  = array('id_parent'     => $this->id_project,
                         'system_serial' => true);

        $this->view->project_result = $ProjectGetChilds->run( $params );

        return $this->view;
    }
}

