<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get lists from id_parent
 *
   USAGE:
   <pre>

   $ListGetChilds = $this->CoreModel('ListGetChilds');

   $params  = array('id_parent'          => bigint,

                    // get only custom attributes which have default_display = true (1)
                    'default_display' => bool,

                    // if set, the function dont include an array var "attributes"
                    'no_transform_attributes' = bool,

                    // "t" for true or "f" for false
                    // get preferred lists only or not or all if not defined
                    //
                    'preferred'       => string,          // optional

                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool); // optional , value: true

   $result  = $ListGetChilds->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = & $result;
   }

   // Each result set contains an additional var 'num_childs':
   // the number of lists which have the current list as parent
   //

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

class ListGetChilds extends    AbstractModel
                    implements InterfaceModel
{
    /**
     * get lists from id_parent
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            // if the system_serial check must be included
            //
            $_system_serial = "";
            if (isset($params['system_serial']) && ($params['system_serial'] == true)) {
                $_system_serial = ", geocontexter.gc_system_is_serial(id_list) AS system_serial";
            }

            // check on preferred lists only
            //
            $_preferred = "";
            if (isset($params['preferred'])) {
                $_preferred = ' AND gil.preferred = ' . $params['preferred'];
            }

            // check on preferred lists only
            //
            $_default_display = true;
            if (isset($params['default_display'])) {
                $_default_display = $params['default_display'];
            }

            // check on preferred lists only
            //
            $_no_transform_attributes = false;
            if (isset($params['no_transform_attributes'])) {
                $_no_transform_attributes = true;
            }


            $sql = 'SELECT  gil.*  '.$_system_serial.',
                            (SELECT count(id_list)
                             FROM geocontexter.gc_list
                             WHERE id_parent = gil.id_list) AS num_childs
                    FROM  geocontexter.gc_list AS gil
                    WHERE gil.id_parent = ?
                    '.$_preferred.'
                    ORDER BY gil.title';

            if (true === $_no_transform_attributes) {
                return $this->query($sql, array($params['id_parent']));
            }

            // json decode of attribute_values
            //

            $result = $this->query($sql, array($params['id_parent']));

            $attr_json = $this->CoreModel('AttributeJsonDecode');

            foreach ($result as & $res) {

                if (($res['id_attribute_group'] == 'NULL') || empty($res['attribute_value'])) {
                    continue;
                }

                $res['attributes'] = $attr_json->decode( $res['attribute_value'], $res['id_attribute_group'], $_default_display );

                if ($res['attributes'] instanceof \Core\Library\Exception) {
                    return $res['attributes'];
                }
            }

            return $result;

        } catch(\Exception $e) {
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
        if (!isset($params['id_parent'])) {
            throw new \Exception('id_parent field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_parent'])) {
            throw new \Exception('id_parent isnt from type bigint');
        }

        if (isset($params['default_display'])) {
            if (false === is_bool($params['default_display'])) {
                throw new \Exception('default_display isnt from type boolean');
            }
        }

        if (isset($params['preferred'])) {
            if (!in_array($params['preferred'], array("t","f"))) {
                throw new \Exception('preferred field must be "t" or "f"');
            }
        }
    }
}