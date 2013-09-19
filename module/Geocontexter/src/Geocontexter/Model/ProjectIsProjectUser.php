<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * check if id_user is assigned to a project
 *
   USAGE:
   <pre>

   $ProjectIsProjectUser = $this->CoreModel('ProjectIsProjectUser');

   $params  = array('id_project' => bigint,
                    'id_user'    => bigint);

   // $result = true or false
   //
   $result  = $ProjectIsProjectUser->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
  \*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectIsProjectUser extends    AbstractModel
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

            $sql = 'SELECT id_project_user AS is_user
                     FROM  geocontexter.gc_project_user
                        WHERE id_project = ?
                           AND   id_user = ?';

            $user = $this->query($sql, array($params['id_project'], $params['id_user']));

            if (isset($user[0]['is_user'])) {
                return true;
            }

            return false;

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

        if (!isset($params['id_user'])) {
            throw new \Exception('id_user field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_user'])) {
            throw new \Exception('id_user isnt from type bigint');
        }
    }
}
