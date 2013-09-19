<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get list item
 *
   USAGE:
   <pre>
   $ItemGet = $this->CoreModel('ItemGet');

   $params  = array('id_item' => bigint,

                     // get only custom attributes which have default_display = true (1)
                    'default_display' => bool,

                    // if set, the function dont include an array var "attributes"
                    'no_transform_attributes' = bool,

                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool); // optional , value: true

   $result  = $ItemGet->run( $params );

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

class ItemGet extends    AbstractModel
              implements InterfaceModel
{
    /**
     *
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        // if the system_serial check must be included
        //
        $_system_serial = "";
        if (isset($params['system_serial']) && ($params['system_serial'] == true)) {
            $_system_serial = ", geocontexter.gc_system_is_serial(id_item) AS system_serial";
        }

        // check on preferred lists only
        //
        $_default_display = null;
        if (isset($params['default_display'])) {
            $_default_display = $params['default_display'];
        }

        // check on preferred lists only
        //
        $_no_transform_attributes = false;
        if (isset($params['no_transform_attributes'])) {
            $_no_transform_attributes = true;
        }

        $sql = 'SELECT  * '.$_system_serial.'
                FROM  geocontexter.gc_item
                WHERE id_item = ?';

        if (true === $_no_transform_attributes) {
            $result = $this->query($sql, array($params['id_item']), true);
            return $result->current();
        }

        $result = $this->query($sql, array($params['id_item']), true);

        $_result = $result->current();

        if (($_result['id_attribute_group'] == 'NULL') || empty($_result['attribute_value'])) {
            return $_result;
        }

        // json decode of content in row "attribute_values"
        //

        $AttributeJsonDecode = $this->CoreModel('AttributeJsonDecode');

        $_result['attributes'] = $AttributeJsonDecode->decode( $_result['attribute_value'], $_result['id_attribute_group'], $_default_display );

        return $_result;
    }

    /**
     * set and validate parameters
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_item'])) {
            throw new \Exception('id_item field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_item'])) {
            throw new \Exception('id_item isnt from type bigint');
        }

        if (isset($params['default_display'])) {
            if (!in_array($params['default_display'], array(true,false,0))) {
                throw new \Exception('default_display must be one of true , false or 0');
            }
        }

        if (isset($params['no_transform_attributes'])) {
            if (false === is_bool($params['no_transform_attributes'])) {
                throw new \Exception('no_transform_attributes isnt from type boolean');
            }
        }
    }
}