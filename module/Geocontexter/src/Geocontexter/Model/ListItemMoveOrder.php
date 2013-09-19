<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Move attribute order
 *
   USAGE:
   <pre>

   $ItemListMoveOrder = $this->CoreModel('ItemListMoveOrder');

   $params  = array('id_item' => bigint);

   $result  = $ItemListMoveOrder->moveUp( $params );
   // or
   // $result  = $ItemListMoveOrder->moveDown( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
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

class ListItemMoveOrder extends AbstractModel
{
    /**
     * move attributeorder up
     *
     *
     * @param array $params
     */
    public function moveUp( $params )
    {
        try {

            $this->validate_params( $params );

            $this->query("SELECT geocontexter.gc_item_move_list_order_up('{$params['id_item']}', '{$params['id_list_item']}')");

        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * move attributeorder down
     *
     *
     * @param array $params
     */
    public function moveDown( $params )
    {
        try {

            $this->validate_params( $params );

            $this->query("SELECT geocontexter.gc_item_move_list_order_down('{$params['id_item']}', '{$params['id_list_item']}')");

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

        if (!isset($params['id_list_item'])) {
            throw new \Exception('id_list_item field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_list_item'])) {
            throw new \Exception('id_list_item isnt from type bigint');
        }
    }
}
