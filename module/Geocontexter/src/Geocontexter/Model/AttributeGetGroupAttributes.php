<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get all attributes of a given attribute group
 *
 *  USAGE:
   <pre>
   $group_attributes = $this->CoreModel('AttributeGetGroupAttributes');

   $params  = array('id_group'        => bigint,
                    'order'           => array, //optional , ex.: array('attribute_order' => 'asc')
                    'default_display' => bool,  //optional , value: true or false

                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial'    => bool); // optional , value: true

   $this->view->result = $group_attributes->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 893 $ / $LastChangedDate: 2011-08-12 09:33:51 +0200 (Fr, 12 Aug 2011) $ / $LastChangedBy: armand.turpel@gmail.com $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeGetGroupAttributes extends    AbstractModel
                                  implements InterfaceModel
{
    private $order = "ORDER BY ";

    /**
     * get all attributes of a given attribute group
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params( $params );

        // if the system_serial check must be included
        //
        $_system_serial = "";
        if (isset($params['system_serial']) && ($params['system_serial'] == true)) {
            $_system_serial = ", geocontexter.gc_system_is_serial(id_group) AS system_serial";
        }

        // get default_display only
        //
        $_sql_default_display = "";
        if (isset($params['default_display'])) {
            if ($params['default_display'] === true) {
                $_sql_default_display = " AND default_display = 't' ";
            } elseif($params['default_display'] === false) {
                $_sql_default_display = " AND default_display = 'f' ";
            }
        }

        $sql = 'SELECT * '.$_system_serial.'
                FROM  geocontexter.gc_attribute
                WHERE id_group = ?
                '.$_sql_default_display.'
                ' . $this->order;

        return $this->query($sql, array($params['id_group']));
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_group'])) {
            throw new \Exception('id_group field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_group'])) {
            throw new \Exception('id_group isnt from type bigint');
        }

        if (isset($params['order'])) {
            $comma = '';
            foreach ($params['order'] as $key => $val) {
                $this->order .= $comma . "{$key} {$val}";
                $comma = ',';
            }
        } else {
          $this->order .= "attribute_order";
        }


    }
}