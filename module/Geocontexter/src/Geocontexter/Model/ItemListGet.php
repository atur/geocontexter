<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get item list from id_list and id_item
 *
   USAGE:
   <pre>

   $ItemListGet = $this->CoreModel('ItemListGet');

   $params  = array('id_list' => bigint,
                    'id_item' => bigint,
                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool); // optional , value: true

   $result  = $ItemListGet->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = & $result;
   }

   // The result set contains an additional var 'branch':
   // the list branch
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

class ItemListGet extends    AbstractModel
                  implements InterfaceModel
{
    /**
     * get list from id_list
     *
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
            $_system_serial = ", geocontexter.gc_system_is_serial(gil.id_list) AS system_serial";
        }

        $sql = 'SELECT  gil.* '.$_system_serial.',
                        gcil.id_list_item,
                        (SELECT array_to_string(array(SELECT title FROM geocontexter.gc_list_get_branch(gil.id_list)),\'/\')) AS branch

                FROM  geocontexter.gc_list AS gil

                INNER JOIN geocontexter.gc_list_item AS gcil
                  ON gil.id_list = gcil.id_list

                WHERE gil.id_list  = ?
                AND   gcil.id_item = ?';

        $result = $this->query($sql, array($params['id_list'], $params['id_item']));
        return $result[0];
    }

    /**
     * set and validate parameters
     *
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

        if (!isset($params['id_item'])) {
            throw new \Exception('id_item field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_item'])) {
            throw new \Exception('id_item isnt from type bigint');
        }
    }
}
