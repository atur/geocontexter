<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get list items from id_list
 *
   USAGE:
   <pre>
   $item_get_list_related = $this->CoreModel('ItemGetListRelated');

   $params  = array('id_list' => bigint,

                    // get only custom attributes which have default_display = true (1)
                    'default_display' => bool,

                    // if set, the function dont include an array var "attributes"
                    'no_transform_attributes' = bool,

                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool); // optional , value: true

    $this->view->result = $item_get_list_related->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 835 $ / $LastChangedDate: 2011-03-05 12:55:03 +0100 (Sa, 05 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ItemGetListRelated extends    AbstractModel
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
            $_system_serial = ", geocontexter.gc_system_is_serial(gci.id_item) AS system_serial";
        }

        // check on preferred lists only
        //
        $_no_transform_attributes = false;
        if (isset($params['no_transform_attributes'])) {
            $_no_transform_attributes = true;
        }

        // check on preferred lists only
        //
        $_default_display = true;
        if (isset($params['default_display'])) {
            $_default_display = $params['default_display'];
        }

        $_sql_limit = '';
        if (isset($params['limit'])) {
            $_sql_limit = 'LIMIT ' . $params['limit'][0] . ' OFFSET ' . $params['limit'][1];
        }

        $this->_sql_where = 'gcilr.id_list = ?';
        $this->_sql_join  = 'INNER';

        if ($params['id_list'] == 0) {
            $this->_sql_where = 'gcilr.id_item IS NULL';
            $this->_sql_join  = 'LEFT';
        }

        $sql = 'SELECT  gci.* '.$_system_serial.'
                FROM  geocontexter.gc_item AS gci
                '.$this->_sql_join.' JOIN geocontexter.gc_list_item AS gcilr
                ON gci.id_item=gcilr.id_item
                WHERE ' . $this->_sql_where . '
                ORDER BY gci.title ' . $_sql_limit;

        $this->id_list = $params['id_list'];

        if (true === $_no_transform_attributes) {
            if ($params['id_list'] == 0) {
                return $this->query( $sql );
            }

            return $this->query( $sql, array($params['id_list']) );
        } else {
            // json decode of attribute_values
            //
            if ($params['id_list'] == 0) {
            //die($sql);
                $result = $this->query( $sql );
            } else {
                $result = $this->query($sql, array($params['id_list']));
            }

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
        }
    }

    /**
     * count total rows without limit
     *
     * @return int total num rows
     * @todo optimize the query
     */
    public function totalNumRows()
    {
        $sql = 'SELECT count(DISTINCT gci.id_item) AS num
                FROM  geocontexter.gc_item AS gci
                '.$this->_sql_join.' JOIN geocontexter.gc_list_item AS gcilr
                ON gci.id_item=gcilr.id_item
                WHERE ' . $this->_sql_where;
//die($sql);
        $result = $this->query($sql, array($this->id_list));

        if (isset($result[0]['num'])) {
            return  (int)$result[0]['num'];
        }

        return 0;
    }

    /**
     * set and validate parameters
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_list'])) {
            throw new \Exception('id_list field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_list'])) {
            throw new \Exception('id_list isnt from type bigint');
        }

        if (isset($params['default_display'])) {
            if (false === is_bool($params['default_display'])) {
                throw new \Exception('default_display isnt from type boolean');
            }
        }

        if (isset($params['no_transform_attributes'])) {
            if (false === is_bool($params['no_transform_attributes'])) {
                throw new \Exception('no_transform_attributes isnt from type boolean');
            }
        }
    }
}