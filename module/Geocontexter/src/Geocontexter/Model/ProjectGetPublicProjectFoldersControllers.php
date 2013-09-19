<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get all public project controllers directories
 *
 *
   USAGE:
   <pre>

   $ProjectGetPublicProjectFoldersControllers = $this->CoreModel('ProjectGetPublicProjectFoldersControllers');

   $result  = $ProjectGetPublicProjectFoldersControllers->run();

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = $result;
   }

   // result array format
   //
   // array('controller_dir'    => string)

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectGetPublicProjectFoldersControllers extends    AbstractModel
                                                implements InterfaceModel
{
    /**
     * @return array Public controllers
     */
    public function run( $params = array() )
    {
        try {

            $result   = array();
            $dir_name = array();
/*
            $_config = $this->config->resources->toArray();

            if (isset($_config['frontController']['controllerDirectory'])) {
                $controller_dir = $_config['frontController']['controllerDirectory'];
            } else {
                $controller_dir = GEOCONTEXTER_ROOT . '/module/Public/src/Public/Controller';
            }
            */
            $project_dir =$controller_dir = GEOCONTEXTER_ROOT . '/module/Public/src/Project/Controller';

            //$project_dir = $controller_dir . '/Project';

            $controller_name = array();

            if (!file_exists($project_dir)) {
                throw new \Exception('Project controller directory dosent exists: ' . $project_dir);
            }

            if ( (($handle = @opendir( $project_dir ))) != FALSE ) {

                while ( (( $dir = readdir( $handle ) )) != false ) {

                    if ( ( $dir == "." ) || ( $dir == ".." ) || ( $dir == ".svn" )) {
                        continue;
                    }
                    if (is_dir($project_dir . '/' . $dir)) {

                        if (false === ($controller_file = file_get_contents($project_dir . '/' . $dir . '/IndexController.php'))) {

                          throw new \Exception('Missing index controller in folder to read: ' . $project_dir . '/' . $dir);

                        } else if(preg_match("/Project_([a-zA-z0-9]+)_IndexController/", $controller_file, $matches)) {

                          $dir_name[] = $matches[1];

                        } else {

                          throw new \Exception('index controller classname missmatch: ' . $project_dir . '/' . $dir . '/IndexController.php');

                        }
                    }
                }
                @closedir( $handle );
            } else {
                throw new \Exception('Can not open controllers folder to read: ' . $project_dir);
            }

            sort( $dir_name );

            $result['controller_dir'] = $dir_name;

            return $result;

        } catch(\Exception $e) {
            throw $e;
        }
    }
}
