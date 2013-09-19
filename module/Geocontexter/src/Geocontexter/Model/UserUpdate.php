<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Update user
 *
   USAGE:
   <pre>

   $UserUpdate = $this->CoreModel('UserUpdate');

   $params  = array('id_user' => bigint,                          // required
                    'data'    => array('user_lastname' => string,
                                       'user_forename' => string,
                                       'user_password' => string,
                                       'user_email'    => string,
                                       'user_url'      => string,
                                       'user_timezone' => string,
                                       'description'   => string,
                                       'id_group'      => bigint,
                                       'id_status'     => int,
                                       'lang'          => string
                                    ));

   $result  = $UserUpdate->run( $params );

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

class UserUpdate extends    AbstractModel
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
     * update context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $this->update('gc_user', 'geocontexter', $params['data'], array('id_user' => $params['id_user']));

            // Trash handling
            //
            //
            $this->delete('gc_trash', 'geocontexter', array('id_item'    => $params['id_user'],
                                                            'table_hash' => 2));

            if ($params['data']['id_status'] == 0) {
                $trash_params = array('id_item'    => $params['id_user'],
                                      'table_hash' => 2,
                                      'trash_time' => time());

                $this->insert('gc_trash', 'geocontexter', $trash_params);
            }

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
        if (!isset($params['data'])) {
            throw new \Exception('data array isnt defined');
        }

        foreach ($params['data'] as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (!isset($params['data']['id_status'])) {
            throw new \Exception('id_status field isnt defined');
        } else {

            $val_int = new \Zend\Validator\Int();

            if (false === $val_digits->isValid($params['data']['id_status'])) {
                throw new \Exception('id_status isnt from type int');
            }
        }

        if (!isset($params['id_user'])) {
            throw new \Exception('user field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_user'])) {
            throw new \Exception('id_user isnt from type bigint');
        }
    }
}