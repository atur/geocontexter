<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Manage project geometries
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 844 $ / $LastChangedDate: 2011-03-24 09:50:17 +0100 (jeu., 24 mars 2011) $ / $Author: armand.turpel $
 */

class Geocontexter_ProjectGeometryController extends Mozend_Controller_Action_AbstractAdmin
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

        $this->view->new_project_geometry_polygon     = '';
        $this->view->new_project_geometry_srid        = '';
        $this->view->new_project_geometry_title       = '';
        $this->view->new_project_geometry_description = '';

        $this->init_data();
    }


    public function indexAction()
    {

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

        $params['id_project']      = $this->id_project;

        $this->view->id_project_geometries =
                    $id_project_geometries = $this->request->getPost('id_project_geometry');

        $add_project_geometry      = $this->request->getPost('add_project_geometry');
        $remove_project_geometries = $this->request->getPost('id_project_geometry_delete');

        if(count($this->view->error) > 0)
        {
            $this->init_data();
            return;
        }

        $project_geometry = $this->request->getPost('new_project_geometry_polygon');

        // add new project geometry
        //
        if($add_project_geometry !== null)
        {
            $this->view->new_project_geometry_polygon     = trim($project_geometry);
            $this->view->new_project_geometry_srid        = trim($this->request->getPost('new_project_geometry_srid'));
            $this->view->new_project_geometry_title       = trim($this->request->getPost('new_project_geometry_title'));
            $this->view->new_project_geometry_description = trim($this->request->getPost('new_project_geometry_description'));

            if(($project_geometry !== null) && !empty($project_geometry))
            {
                $srid = $this->view->new_project_geometry_srid;

                if(($srid !== null) && !empty($srid))
                {
                    $params['srid'] = (int)$srid;
                }

                $params['title']            = $this->view->new_project_geometry_title;
                $params['description']      = $this->view->new_project_geometry_description;
                $params['project_geometry'] = $this->filter_geometry($project_geometry);

                $project_geometry = new Geocontexter_Model_ProjectGeometryAdd;

                $result = $project_geometry->add( $params );

                if($result instanceof Mozend_ModelError)
                {
                   //$this->error( $result->getErrorString(), __file__, __line__ );

                   $this->view->error[] = 'Probably the polygon geometry isnt correct. Please verify.';
                }
                else
                {
                    $this->_redirect($this->view->adminAreaToken .
                                     '/geocontexter/project-geometry/index/id_project/' . $this->id_project);
                }
            }
            else
            {
                $this->view->error[] = 'Geometry polygon is empty';
            }
        }

        $this->view->update = $this->request->getPost('update');

        if( ($this->view->update !== null)    &&
            ($id_project_geometries !== null) &&
            is_array($id_project_geometries))
        {
            $x = 0;

            $this->view->update_project_geometry_title =
                               $project_geometry_title = $this->request->getPost('project_geometry_title');

            $this->view->update_project_geometry_description =
                               $project_geometry_description = $this->request->getPost('project_geometry_description');

            $this->view->update_project_geometry_polygon =
                                       $project_geometry = $this->request->getPost('project_geometry');

            $this->view->update_project_geometry_srid =
                               $project_geometry_srid = $this->request->getPost('project_geometry_srid');

            foreach($id_project_geometries as $id_project_geometry)
            {
                $project = new Geocontexter_Model_ProjectGeometryUpdate;

                $params['id_project_geometry'] = $id_project_geometry;

                $params['data'] = array('title'            => (string)$project_geometry_title[$x],
                                        'description'      => (string)$project_geometry_description[$x],
                                        'project_geometry' => (string)$project_geometry[$x],
                                        'srid'             => (int)$project_geometry_srid[$x]);

                $result = $project->update( $params );

                if($result instanceof Mozend_ModelError)
                {
                    $this->error( $result->getErrorString(), __file__, __line__ );
                    $this->view->error[] = 'Some errors occurred. Please verify required geometries polygons.';
                }

                $x++;
            }
        }

        if(count($this->view->error) > 0)
        {
            return;
        }

        // remove project geometries
        //
        if(($remove_project_geometries !== null) && is_array($remove_project_geometries))
        {
            foreach($remove_project_geometries as $id_project_geometry)
            {
                $project = new Geocontexter_Model_ProjectGeometryDelete;

                $params['id_project_geometry'] = $id_project_geometry;

                $result = $project->delete( $params );

                if($result instanceof Mozend_ModelError)
                {
                   $this->view->error[] = $result->getErrorString();
                }
            }

            if(count($this->view->error) > 0)
            {
                return $this->error( $result->getErrorString(), __file__, __line__ );
            }
        }

        if(count($this->view->error) > 0)
        {
            return;
        }

        $this->_redirect($this->view->adminAreaToken .
                         '/geocontexter/project-edit/index/id_project/' . $this->id_project);
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

        $project_geometry = new Geocontexter_Model_ProjectGeometryGet;

        $params           = array('id_project'    => $this->id_project,
                                  'system_serial' => true);

        $result_project_geometry  = $project_geometry->get( $params );

        if($result_project_geometry instanceof Mozend_ModelError)
        {
           return $this->error( $result_project_geometry->getErrorString(), __file__, __line__ );
        }
        else
        {
           $this->view->result_project_geometry = $result_project_geometry;
        }
    }

    public function filter_geometry( $str )
    {
        return trim(preg_replace("/[ ]+/"," ",preg_replace("/[ ]*,[ ]*/",",",preg_replace("/[^ 0-9\.,]/","",$str))));
    }
}

