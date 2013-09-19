<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new context
 *
   USAGE:
   <pre>

   $UserAdd = $this->CoreModel('UserAdd');

   $params  = array('title'       => string,
                    'description' => string,
                    'id_parent'   => bigint,
                    'id_owner'    => bigint,
                    'id_status'   => smallint);

   $result  = $UserAdd->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
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

class UserAdd extends    AbstractModel
              implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('user_lastname' => true,
                                    'user_forename' => true,
                                    'user_login'    => true,
                                    'user_password' => true,
                                    'user_email'    => true,
                                    'user_url'      => true,
                                    'user_timezone' => true,
                                    'description'   => true,
                                    'id_group'      => true,
                                    'id_status'     => true,
                                    'lang'          => true
                                    );

    /**
     * add context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $this->insert('gc_user', 'geocontexter', $params);

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
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
        foreach ($params as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (!isset($params['user_login'])) {
            throw new \Exception('User login field isnt defined');
        }

        if (!isset($params['id_status'])) {
            throw new \Exception('id_status field isnt defined');
        }
        else
        {
            $val_int = new \Zend\Validator\Int();

            if (false === $val_int->isValid($params['id_status'])) {
                throw new \Exception('id_status isnt from type int');
            }
        }
    }
}