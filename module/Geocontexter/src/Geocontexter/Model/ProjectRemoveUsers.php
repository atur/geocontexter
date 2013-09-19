<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Remove users from project
 *
   USAGE:
   <pre>

   $ProjectRemoveUsers = $this->CoreModel('ProjectRemoveUsers');

   $params  = array('id_project' => bigint,
                    'id_user'    => array);

   $result  = $ProjectRemoveUsers->run( $params );

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

class ProjectRemoveUsers extends    AbstractModel
                         implements InterfaceModel
{
    /**
     * remove project users
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $this->beginTransaction();

            $_delete_action = false;

            $val_digits = new \Zend\Validator\Digits();

            foreach ($params['id_user'] as $user) {

                if (false === $val_digits->isValid($user)) {
                    throw new \Exception('id_user isnt from type bigint');
                }

                $this->delete('gc_project_user', 'geocontexter', array('id_project' => $params['id_project'], 'id_user' => $user));
                $_delete_action = true;
            }

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * rollback transaction
     *
     */
    private function rollback()
    {
        if ($this->add_action === true) {
            $this->rollback();
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
        if (!isset($params['id_user'])) {
            throw new \Exception('id_user var isnt defined');
        }

        if (!is_array($params['id_user'])) {
            return;
        }

        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}
