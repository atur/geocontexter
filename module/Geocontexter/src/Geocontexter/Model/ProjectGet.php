<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get project from id_project
 *
   USAGE:
   <pre>

   $ProjectGet = $this->CoreModel('ProjectGet');

   $params  = array('id_project' => bigint,
                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool); // optional , value: true

    // return false if there is no result
   $result  = $ProjectGet->run( $params );


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

class ProjectGet extends    AbstractModel
                 implements InterfaceModel
{
    /**
     * get project from id_project
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
                $_system_serial = ", geocontexter.gc_system_is_serial(gp.id_project) AS system_serial";
            }

            $sql = 'SELECT  gp.* '.$_system_serial.'
                    FROM  geocontexter.gc_project AS gp
                    WHERE gp.id_project = ?';

            $result = $this->query($sql, array($params['id_project']), true);

            return $result->current();

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
        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_project']))  {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}
