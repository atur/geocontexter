<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * decode a given json attribute encoded string into an php array
 *
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */
namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class AttributeJsonDecode extends AbstractModel
{
    /**
     * decode a given json attribute encoded string.
     *
     * it returns an array with ordered attributes
     *
     * $result[x]['info']  // attribute information
     * $result[x]['value'] // attribute value
     *
     * @param  string $attributes_serialized_array serialiued var of attributes
     * @param  bigint $id_attribute_group
     * @param  int    $default_display  get only default display attributes
     * @return array
     */
    public function decode( $attributes_serialized_array, $id_attribute_group, $default_display = null )
    {
        $_result = array();

        $__attribute_values = \Zend\Json\Json::decode( $attributes_serialized_array, \Zend\Json\Json::TYPE_ARRAY );

        $attributes_info = $this->get_attributes_info( $id_attribute_group, $default_display );

        if (count($attributes_info) == 0) {
            return $_result;
        }

        foreach ($attributes_info as $attr) {
            $_result[$attr['attribute_name']]['info']  = $attr;

            if (isset($__attribute_values[$attr['attribute_name']])) {
                if ($attr['multi_value'] == false) {
                    $_result[$attr['attribute_name']]['value'] = $__attribute_values[$attr['attribute_name']];
                } else {
                    $_result[$attr['attribute_name']]['value'] = $__attribute_values[$attr['attribute_name']];
                }
            } else {
                $_result[$attr['attribute_name']]['value'] = null;
            }
        }

        return $_result;
    }

    /**
     * get info about attributes of a group by order
     *
     * @param  bigint $id_attribute_group
     * @param  int    $default_display
     * @return array
     */
    private function get_attributes_info( $id_attribute_group, $default_display )
    {
        $var = 'attribute_' . $id_attribute_group;

        if (isset($this->$var)) {
            return $this->$var;
        }

        $group_attributes = $this->CoreModel('AttributeGetGroupAttributes');

        $params  = array('id_group'        => $id_attribute_group,
                         'order'           => array('attribute_order' => 'asc'));

        if ($default_display !== null) {
            $params['default_display'] = $default_display;
        }

        $result  = $group_attributes->run( $params );

        $this->$var = $result;

        return $result;
    }
}
