<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * search for lists
 *
   USAGE:
   <pre>

   $ListSearch = $this->CoreModel('ListSearch');

   $params  = array('search'          => string,
                    // get default custom attributes: 1 = get them, 2 = get non default, default = all
                    'default_attributes' => integer,

                    // "t" for true or "f" for false
                    // get preferred lists only or not or all if not defined
                    //
                    'preferred'       => string,          // optional

                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool); // optional , value: true

   $result  = $ListSearch->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = $result;
       $totalNumRows = $ListSearch->totalNumRows();
   }

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

class ListSearch extends    AbstractModel
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
            if ($params['system_serial'] == true) {
                $_system_serial = ", geocontexter.gc_system_is_serial(gil.id_list) AS system_serial";
            }

            $_sql_limit = '';
            if (isset($params['limit'])) {
                $_sql_limit = 'LIMIT ' . $params['limit'][0] . ' OFFSET ' . $params['limit'][1];
            }

            // check on preferred lists only
            //
            $_preferred = "";
            if (isset($params['preferred'])) {
                $_preferred = ' AND gil.preferred = ' . $params['preferred'];
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

            $sql = 'SELECT  gil.*  '.$_system_serial.' ,
                            (SELECT array_to_string(ARRAY(SELECT title FROM geocontexter.gc_list_get_branch(gil.id_list)),\'/\') ) AS branch

                    FROM  geocontexter.gc_list AS gil
                    WHERE gil.title ILIKE ?

                    '.$_preferred.'

                    ORDER BY gil.title ' . $_sql_limit;

            if (true === $_no_transform_attributes) {
                return $this->query($sql, array($this->search . '%'));
            }

            // json decode of attribute_values
            //

            $result = $this->query($sql, array($this->search . '%'));

            $attr_json = $this->CoreModel('AttributeJsonDecode');

            foreach ($result as & $res) {

                if (($res['id_attribute_group'] == 'NULL') || empty($res['attribute_value'])) {
                    continue;
                }

                $res['attributes'] = $attr_json->decode( $res['attribute_value'], $res['id_attribute_group'], $_default_display );
            }

            if ($res['attributes'] instanceof \Core\Library\Exception) {
                throw new \Exception($res['attributes']->getMessage());
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
     */
    public function totalNumRows()
    {
        try {

            $sql = 'SELECT  count(id_list) AS num_lists
                    FROM  geocontexter.gc_list
                    WHERE title LIKE ?';

            $res = $this->query($sql, array($this->search . '%'));
            return (int)$res[0]['num_lists'];

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

        if (isset($params['default_attributes'])) {

            if (false === is_int($params['default_attributes'])) {
                throw new \Exception('default_attributes isnt from type integer');
            }

            if (($params['default_attributes'] < 1) || ($params['default_attributes'] > 2)) {
                throw new \Exception('default_attributes value must be 1 or 2');
            }

            $this->default_attributes = $params['default_attributes'];

        } else {

            $this->default_attributes = 0;

        }

        if (isset($params['preferred'])) {

            if (!in_array($params['preferred'], array("t","f"))) {
                throw new \Exception('preferred field must be "t" or "f"');
            }
        }
    }
}