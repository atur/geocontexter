<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get all projects
 *
   USAGE:
   <pre>

   $ProjectGetAll = $this->CoreModel('ProjectGetAll');

   $params  = array('has_controller' => bool,   // if true, a project must have an assigned public controller
                    'id_status'      => array); // optional , ex: array('>=', 200) // project which are at least active

   $result  = $ProjectGetAll->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = $result;
   }
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

class ProjectGetAll extends    AbstractModel
                    implements InterfaceModel
{
    /**
     * get all projects
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            // if the system_serial check must be included
            //
            $_system_serial = "";
            if (isset($params['system_serial']) && $params['system_serial'] == true) {
                $_system_serial = ", geocontexter.gc_system_is_serial(id_project) AS system_serial";
            }

            $_sql_and = "";

            if (isset($params['has_controller']) && (true === $params['has_controller'])) {
                $_sql_and = "AND gp.controller IS NOT NULL\n";
            }

            if (isset($params['id_status'])) {
                $_sql_and .= "AND gp.id_status {$params['id_status'][0]} {$params['id_status'][1]}\n";
            }

            $sql = 'SELECT  gp.* '.$_system_serial.' ,
                            (SELECT array_to_string(array(SELECT title FROM geocontexter.gc_project_get_branch(gp.id_project)),\'/\')) AS branch
                    FROM  geocontexter.gc_project AS gp
                    WHERE 1=1
                    '.$_sql_and.'
                    ORDER BY  branch, gp.title';

            return $this->query($sql);

        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (isset($params['has_controller'])) {
            if(!is_bool($params['has_controller']))
            {
                throw new \Exception('has_controller parameter isnt from type boolean');
            }
        }

        if (isset($params['id_status'])) {

            if (!isset($params['id_status'][0])) {
                throw new \Exception('id_status array index 0 value not set');
            }
            if (!isset($params['id_status'][1]))  {
                throw new \Exception('id_status array index 1 value not set');
            }

            if(!in_array($params['id_status'][0], array(">","<",">=","<=","=","!="))) {
                throw new \Exception('id_status array index 0 value is wrong: ' . var_export($params['id_status'][0],true));
            }

            if (!in_array($params['id_status'][1], array(0,100,200,300))) {
                throw new \Exception('id_status array index 1 value is wrong: ' . var_export($params['id_status'][1],true));
            }
        }
    }
}