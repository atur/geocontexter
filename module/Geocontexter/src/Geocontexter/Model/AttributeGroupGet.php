<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get attribute group
 *
 * USAGE:
 *  <pre>
    $attribute_group_get = $this->CoreModel('AttributeGroupGet');

   $params = array('id_group'     => bigint,  // required

                   // if set, the result contains a bool variable "has_relation"
                   // with info if the attribute group is associated with record items
                   'has_relation' => true,

                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool);

   $this->view->result = $attribute_group_get->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeGroupGet extends   AbstractModel
                                  implements InterfaceModel
{
    /**
     * get attribute group
     *
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        $_table = "geocontexter.gc_attribute_group";

        // if the system_serial check must be included
        //
        $_system_serial = "";
        if (isset($params['system_serial']) && ($params['system_serial'] == true)) {
            $_system_serial = ", geocontexter.gc_system_is_serial(id_group) AS system_serial";
        }

        // if the group has content relation
        //
        $_has_relation = "";
        if (isset($params['has_relation']) && ($params['has_relation'] == true)) {
            $_has_relation = ", geocontexter.gc_attribute_group_has_relation(id_group, id_table) AS has_relation";
        }

        $sql = 'SELECT  * '.$_system_serial.$_has_relation.'
                FROM  geocontexter.gc_attribute_group
                WHERE id_group=?';

        $result = $this->query($sql, array($params['id_group']));

        return $result[0];
    }

    /**
     * set and validate parameters
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_group'])) {
            throw new \Exception('id_group isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_group'])) {
            throw new \Exception('id_group isnt from type bigint');
        }


    }
}