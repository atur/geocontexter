<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new attribute
 *
 *  USAGE:
  <pre>
   $attribute_add = $this->CoreModel('AttributeAdd');

   $params  = array('id_attribute'          => bigint,
                    'id_group'              => bigint,  // required
                    'id_status'             => int,
                    'default_display'       => bool,
                    'multi_value'           => bool,
                    'lang'                  => string,
                    'update_time'           => string,
                    'attribute_name'        => string,  // required
                    'attribute_title'       => string,
                    'attribute_description' => string,
                    'attribute_required'    => bool,
                    'attribute_type'        => string,  // required
                    'attribute_order'       => int,
                    'attribute_unit'        => string,
                    'attribute_regex'       => string,
                    'attribute_html_type'   => string);

   $attribute_add->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 838 $ / $LastChangedDate: 2011-03-17 21:16:20 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeAdd extends    AbstractModel
                   implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_attribute'            => true,
                                    'id_group'                => true,
                                    'id_status'               => true,
                                    'default_display'         => true,
                                    'multi_value'             => true,
                                    'lang'                    => true,
                                    'update_time'             => true,
                                    'attribute_name'          => true,
                                    'attribute_title'         => true,
                                    'attribute_description'   => true,
                                    'attribute_required'      => true,
                                    'attribute_type'          => true,
                                    'attribute_order'         => true,
                                    'attribute_unit'          => true,
                                    'attribute_regex'         => true,
                                    'attribute_html_type'     => true
                                    );

    /**
     * add attribute
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params( $params );

            if (!isset($params['attribute_order'])) {
                $params['attribute_order'] =
                    new \Zend\Db\Sql\Expression("geocontexter.gc_get_new_entry_order(
                                                      'gc_attribute',
                                                      'id_group',
                                                      {$params['id_group']},
                                                      'attribute_order')"
                                                );
            }

            if (!isset($params['update_time'])) {
                $params['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
            }

            $this->insert('gc_attribute', 'geocontexter', $params);

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

        if (!isset($params['attribute_name'])) {
            throw new \Exception('attribute_name field isnt defined');
        }

        if (empty($params['attribute_name'])) {
            throw new \Exception('attribute_name is empty');
        }

        if (!isset($params['attribute_type'])){
            throw new \Exception('attribute_type field isnt defined');
        }

        if (empty($params['attribute_type'])) {
            throw new \Exception('attribute_type is empty');
        }

        if (!isset($params['id_group'])) {
            throw new \Exception('id_group field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_group'])) {
            throw new \Exception('id_group isnt from type bigint');
        }

        if (isset($params['update_time'])) {
            $val_date = new \Zend\Validator\Date(array('format' => 'Y-m-d H:i:s'));

            if (true !== $val_date->isValid($params['update_time'])) {
                throw new \Exception('update_time has wrong format: ' . $params['update_time']);
            }
        }


    }
}
