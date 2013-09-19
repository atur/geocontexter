<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get project users
 *
   USAGE:
   <pre>

   $UserGetProjectRelated = $this->CoreModel('UserGetProjectRelated');

   $params  = array('id_project' => bigint,

                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_list is within the system serial
                   // else null
                   'system_serial' => bool);  // optional , value: true

   $result_users  = $UserGetProjectRelated->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = & $result;
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

class UserGetProjectRelated extends    AbstractModel
                            implements InterfaceModel
{
    /**
     * get lists from id_parent
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
            if ($params['system_serial'] == true) {
                $_system_serial = ", geocontexter.gc_system_is_serial(gu.id_user) AS system_serial";
            }

            $sql = 'SELECT  gu.*  '.$_system_serial.'

                    FROM geocontexter.gc_project_user AS gpur

                    INNER JOIN geocontexter.gc_user AS gu
                    ON gpur.id_user = gu.id_user

                    WHERE gpur.id_project = ?

                    ORDER BY gu.user_lastname, gu.user_forename';

            return $this->query($sql, array($params['id_project']));

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

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}