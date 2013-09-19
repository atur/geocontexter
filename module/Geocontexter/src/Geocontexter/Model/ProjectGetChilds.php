<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get projects from id_parent
 *
   USAGE:
   <pre>

   $ProjectGetChilds = $this->CoreModel('ProjectGetChilds');

   $params  = array('id_parent' => bigint,
                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool); // optional , value: true

   $result  = $ProjectGetChilds->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = & $result;
   }

   // Each result set contains an additional var 'num_childs':
   // the number of projects which have the current project as parent
   //

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

class ProjectGetChilds extends    AbstractModel
                       implements InterfaceModel
{
    /**
     * get projects from id_parent
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
            if (isset($params['system_serial']) && ($params['system_serial'] == true)) {
                $_system_serial = ", geocontexter.gc_system_is_serial(id_project) AS system_serial";
            }

            $_sql_and = "";

            if (isset($params['has_controller'])) {
                $_sql_and = "AND gp.controller IS NOT NULL\n";
            }

            if (isset($params['status'])) {
                $_sql_and .= "AND gp.id_status {$params['status'][0]} {$params['status'][1]}\n";
            }

            $sql = 'SELECT  gp.* '.$_system_serial.' ,
                            (SELECT count(id_project)
                             FROM geocontexter.gc_project
                             WHERE id_parent = gp.id_project) AS num_childs
                    FROM  geocontexter.gc_project AS gp
                    WHERE gp.id_parent = ?
                    '.$_sql_and.'
                    ORDER BY gp.title';

            return $this->query($sql, array($params['id_parent']));

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
        if (!isset($params['id_parent'])) {
            throw new \Exception('id_parent field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_parent'])) {
            throw new \Exception('id_parent isnt from type bigint');
        }

        if (isset($params['status'])) {
            if (!isset($params['status'][0])) {
                throw new \Exception('status array index 0 value not set');
            }
            if (!isset($params['status'][1])) {
                throw new \Exception('status array index 1 value not set');
            }

            if (!in_array($params['status'][0], array(">","<",">=","<=","=","!="))) {
                throw new \Exception('status array index 0 value is wrong: ' . var_export($params['status'][0],true));
            }

            if (!in_array($params['status'][1], array(0,100,200,300))) {
                throw new \Exception('status array index 1 value is wrong: ' . var_export($params['status'][1],true));
            }
        }
    }
}