<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get item lists
 *
   USAGE:
   <pre>

   $ListGetItemRelated = $this->CoreModel('ListGetItemRelated');

   $params  = array('id_item' => bigint,

                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_list is within the system serial
                   // else null
                   //
                   'system_serial' => bool,              // optional , value: true

                   // result filter for preferred list
                   // true = get only preferred list
                   // false = get non preferred list
                   // if not defined get all
                   //
                   'preferred_order' => bool,            // optional

                   // "t" for true or "f" for false
                   // get preferred lists only or not or all if not defined
                   //
                   'preferred'       => string,          // optional

                   // if true, result is ordered by preferred list flag and title
                   //
                   'order_by_preferred_order' => bool);  // optional

   $result  = $ListGetItemRelated->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = & $result;
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

class ListGetItemRelated extends    AbstractModel
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
                $_system_serial = ", geocontexter.gc_system_is_serial(gil.id_list) AS system_serial";
            }

            // item preferred list filter option
            //
            $_preferred_order_limit = "";
            if (isset($params['preferred_order_limit'])) {
                $_preferred_order_limit = "LIMIT " . $params['preferred_order_limit'];
            }

            // order by preferred list flag first
            //
            $_order_by_preferred_order = "";
            if (isset($params['order_by_preferred_order'])) {
                $_order_by_preferred_order = 'gilr.preferred_order ASC,';
            }

            // check on preferred lists only
            //
            $_preferred = "";
            if (isset($params['preferred'])) {
                $_preferred = " AND gil.preferred = '" . $params['preferred'] ."' ";
            }

            $sql = 'SELECT
                            gil.*  '.$_system_serial.',
                            (SELECT array_to_string(array(SELECT title FROM geocontexter.gc_list_get_branch(gil.id_list)),\'/\')) AS branch,
                            gilr.preferred_order,
                            gilr.id_list_item,
                            (SELECT title FROM geocontexter.gc_item WHERE id_item = gilr.id_item) AS item_title

                    FROM geocontexter.gc_list_item AS gilr

                    INNER JOIN geocontexter.gc_list AS gil
                    ON gilr.id_list = gil.id_list

                    WHERE gilr.id_item = ?

                    '.$_preferred.'

                    ORDER BY '.$_order_by_preferred_order.' gil.title';

            return $this->query($sql, array($params['id_item']));

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
        if (!isset($params['id_item'])) {
            throw new \Exception('id_item field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_item'])) {
            throw new \Exception('id_item isnt from type bigint');
        }

        if (isset($params['preferred'])) {
            if (!in_array($params['preferred'], array("t","f"))) {
                throw new \Exception('preferred field must be "t" or "f"');
            }
        }
    }
}