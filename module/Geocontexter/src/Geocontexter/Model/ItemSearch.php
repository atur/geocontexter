<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * search for list items
 *
   USAGE:
   <pre>

   $ItemSearch = $this->CoreModel('ItemSearch');

   $params  = array('search'          => string,
                    // limit search to id_list(s) and all its descendend id_lists
                    'in_id_list'      => bigint as string or many bigint as string in array,
                    // get default custom attributes: true = get them, false = get non default
                    'default_display' => bool,
                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool); // optional , value: true

   $this->view->result = $ItemSearch->run( $params );

   $totalNumRows = $ItemSearch->totalNumRows();


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

class ItemSearch extends    AbstractModel
                 implements InterfaceModel
{
    /**
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
                $_system_serial = ", geocontexter.gc_system_is_serial(gil.id_item) AS system_serial";
            }

            $_sql_limit = '';
            if (isset($params['limit'])) {
                $_sql_limit = 'LIMIT ' . $params['limit'][0] . ' OFFSET ' . $params['limit'][1];
            }

            $this->_sql_in_id_list = '';
            $this->_sql_tables = '';
            if (isset($params['in_id_list'])) {
                if (is_array($params['in_id_list'])) {
                    $this->_sql_in_id_list = 'AND index.id_parent IN ('.implode(",",$params['in_id_list']).')';
                } else {
                    $this->_sql_in_id_list = 'AND index.id_parent = ' . $params['in_id_list'];
                }


                $this->_sql_tables  = "INNER JOIN geocontexter.gc_list_item  AS gcil  ON gcil.id_item  = gil.id_item\n";
                $this->_sql_tables .= "INNER JOIN geocontexter.gc_list_index AS index ON index.id_list = gcil.id_list\n";
            }

            // check on preferred lists only
            //
            $_default_display = null;
            if (isset($params['default_display'])) {
                $_default_display = $params['default_display'];
            }

            $_no_transform_attributes = false;
            if (isset($params['no_transform_attributes'])) {
                $_no_transform_attributes = true;
            }


            $sql = 'SELECT DISTINCT gil.*  '.$_system_serial.'
                    FROM  geocontexter.gc_item AS gil
                         '.$this->_sql_tables.'
                    WHERE gil.title ILIKE ?
                         '.$this->_sql_in_id_list.'
                    ORDER BY gil.title ' . $_sql_limit;

            if (true === $_no_transform_attributes) {
                return $this->query($sql, array($this->search . '%'));
            }

            // json decode of attribute_values
            //

            $result = $this->query($sql, array($this->search . '%'));

            $AttributeJsonDecode = $this->CoreModel('AttributeJsonDecode');

            foreach ($result as & $res) {
                if (($res['id_attribute_group'] == 'NULL') || empty($res['attribute_value'])) {
                    continue;
                }

                $res['attributes'] = $AttributeJsonDecode->decode( $res['attribute_value'], $res['id_attribute_group'], $_default_display );

                if ($res['attributes'] instanceof \Core\Library\Exception) {
                    throw new \Exception($res['attributes']->getMessage());
                }
            }

            return $result;

        } catch(\Exception $e) {
            throw $e;
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
        try {

            $sql = 'SELECT DISTINCT gil.id_item
                    FROM  geocontexter.gc_item AS gil
                    '.$this->_sql_tables.'
                    WHERE gil.title ILIKE ?
                    '.$this->_sql_in_id_list;

            $res = $this->query($sql, array($this->search . '%'));
            return (int)count($res);

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
        if (!isset($params['search'])) {
            throw new \Exception('search field isnt defined');
        } else {
            $this->search = $params['search'];
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