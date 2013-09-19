<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new List
 *
   USAGE:
   <pre>

   $ListAdd = $this->CoreModel('ListAdd');

   $params  = array('title'              => string,
                    'description'        => string,
                    'id_parent'          => bigint (string),
                    'id_status'          => smallint,
                    'preferred'          => boolean,
                    'id_attribute_group' => bigint (string),
                    'lang'               => string,
                    'update_time'        => string,
                    'attribute_value'    => string);

   $new_id_list  = $ListAdd->run( $params );

   if ($new_id_list instanceof \Core\Library\Exception) {
       return $this->error( $new_id_list->getMessage(), __file__, __line__);
   }

   // return new created id_list or error object

   </pre>
 * @package GeoContexter
 * @subpackage Module_GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ListAdd extends    AbstractModel
              implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_list'            => true,
                                    'title'              => true,
                                    'description'        => true,
                                    'id_parent'          => true,
                                    'id_status'          => true,
                                    'preferred'          => true,
                                    'id_attribute_group' => true,
                                    'lang'               => true,
                                    'update_time'        => true,
                                    'attribute_value'    => true
                                    );

    /**
     * add List
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            if (!isset($params['update_time'])) {
                $params['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
            }

            $this->insert('gc_list', 'geocontexter', $params);

            $id_list = $this->query("SELECT currval('geocontexter.seq_gc_list') as id_keyword");

            $this->db->query('SELECT geocontexter.gc_list_index_add('.$id_list.')');

            $this->commit();

            return $id_list[0]['id_keyword'];

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

        if (!isset($params['title'])) {
            throw new \Exception('List title field isnt defined');
        }

        if (empty($params['title'])) {
            throw new \Exception('List title is empty');
        }

        if (isset($params['id_parent'])) {

            $val_digits = new \Zend\Validator\Digits();

            if (false === $val_digits->isValid($params['id_parent'])) {
                throw new \Exception('id_parent isnt from type bigint');
            }
        }

        if (isset($params['preferred'])) {
            if (!is_bool($params['preferred'])) {
                throw new \Exception('"preferred" isnt from type boolean');
            } else {
                if ($params['preferred'] === true) {
                    $params['preferred'] = "t";
                } else {
                    $params['preferred'] = "f";
                }
            }
        }

        if (isset($params['update_time'])) {

            $val_date = new \Zend\Validator\Date(array('format' => 'yyyy-MM-dd HH:mm:ss'));

            if (true !== $val_date->isValid($params['update_time'])) {
                throw new \Exception('update_time has wrong format: ' . $params['update_time']);
            }
        }


    }
}
