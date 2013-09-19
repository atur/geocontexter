<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new Item
 *
   USAGE:
   <pre>
    $item_add = $this->CoreModel('ItemAdd');

    $params  = array('id_list'            => bigint,
                     'preferred_list'     => bool,
                     'data' => array('title'              => string,
                                     'lang'               => string,
                                     'id_status'          => int,      // smallint
                                     'synonym_of'         => string,   // bigint
                                     'update_time'        => string,
                                     'id_attribute_group' => string,   // bigint
                                     'attribute_value'    => string)); // json format

    // $id_item contains the key of the new created item
    $id_item = $item_add->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 880 $ / $LastChangedDate: 2011-08-08 18:52:54 +0200 (Mo, 08 Aug 2011) $ / $LastChangedBy: armand.turpel@gmail.com $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ItemAdd extends AbstractModel
                      implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_item'            => true,
                                    'title'              => true,
                                    'description'        => true,
                                    'id_status'          => true,
                                    'synonym_of'         => true,
                                    'lang'               => true,
                                    'update_time'        => true,
                                    'id_attribute_group' => true,
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

            if (!isset($params['data']['update_time'])) {
                $params['data']['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
            }

            $this->insert('gc_item','geocontexter', $params['data']);

            $id_item = $this->query("SELECT currval('geocontexter.seq_gc_item') AS id_item");

            if (isset($params['id_list'])) {
                $data = array('id_item'         => $id_item[0]['id_item'],
                              'id_list'         => $params['id_list'],
                              'preferred_order' => new \Zend\Db\Sql\Expression("geocontexter.gc_get_new_entry_order('gc_list_item',
                                                                                                                    'id_list',
                                                                                                                    {$params['id_list']})"));

                $result = $this->insert('gc_list_item', 'geocontexter', $data);


            }

            $this->commit();

            return $id_item[0]['id_item'];

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
        foreach ($params['data'] as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (!isset($params['data']['title'])) {
            throw new \Exception('Item title field isnt defined');
        }

        if (empty($params['data']['title'])) {
            throw new \Exception('Item title is empty');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (isset($params['id_list'])) {
            if (false === $val_digits->isValid($params['id_list'])) {
                throw new \Exception('Item id_list isnt from type bigint');
            }
        }

        if (isset($params['data']['id_attribute_group'])) {
            if (false === $val_digits->isValid($params['data']['id_attribute_group'])) {
                throw new \Exception('id_attribute_group isnt from type bigint');
            }
        }

        if (isset($params['data']['synonym_of'])) {
            if (false === $val_digits->isValid($params['data']['synonym_of'])) {
                throw new \Exception('synonym_of isnt from type bigint');
            }
        }

        if (isset($params['data']['update_time'])) {
            $val_date = new \Zend\Validator\Date(array('format' => 'yyyy-MM-dd HH:mm:ss'));

            if (true !== $val_date->isValid($params['data']['update_time'])) {
                throw new \Exception('update_time has wrong format: ' . $params['data']['update_time']);
            }
        }
    }
}
