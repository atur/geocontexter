<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 *
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
*/

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeJsonEncode extends    AbstractModel
{
    /**
     * json encode of additional attributes
     *
     *
     * @param bigint $attribute_id_group
     * @param array  $vars Array containing the additional attribute values.
     * @param string $var_names_prefix Attribute value names start with prefix. ex.: "a_" e.g.: "a_varname"
     * @return array or error object
     */
    public function encode( $attribute_id_group, $vars, $var_names_prefix = 'a_' )
    {
        $this->fetch_group_attributes( $attribute_id_group );

        $result = array();

        foreach ($this->attributes as $attr) {
            $_name = $var_names_prefix . $attr['attribute_name'];

            if (isset($vars->$_name)) {
                if ($attr['multi_value'] == false)  {
                    $result[$attr['attribute_name']] = $vars->$_name;
                } else {
                    $result[$attr['attribute_name']] = preg_split("/[\r\n]+/", trim($vars->$_name));
                }
            }
        }
        return \Zend\Json\Json::encode($result);
    }

    /**
     * get group attributes by sort order
     *
     * @param bigint $attribute_id_group
     * @param string $order 'order' or 'index' asc
     */
    private function fetch_group_attributes( $attribute_id_group )
    {
        $__result = 'attr_id_' . $attribute_id_group;

        if (isset($this->$__result)) {
            $this->attributes = $this->$__result;
            $this->numAttributes = count($this->attributes);
            return;
        }

        $group_attributes = $this->CoreModel('AttributeGetGroupAttributes');

        $params = array('id_group' => $attribute_id_group,
                        'order'    => array('attribute_order' => 'asc'));

        $this->attributes = $group_attributes->run( $params );

        $this->$__result = $this->attributes;

        $this->numAttributes = count($this->attributes);
    }
}
