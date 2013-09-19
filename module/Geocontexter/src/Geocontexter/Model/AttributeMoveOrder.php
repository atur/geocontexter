<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Move attribute order
 *
 *  USAGE:
   <pre>
   $attribute_move_order = $this->CoreModel('AttributeMoveOrder');

   $params  = array('id_attribute' => bigint, 'id_group' => bigint);

   $result  = $attribute_move_order->moveUp( $params );
   // or
   // $result  = $attribute_move_order->moveDown( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class AttributeMoveOrder extends AbstractModel
{
    /**
     * move attributeorder up
     *
     *
     * @param array $params
     */
    public function moveUp( $params )
    {
        $this->validate_params( $params );

        $this->query("SELECT geocontexter.gc_attribute_move_order_up('{$params['id_attribute']}', '{$params['id_group']}')");
    }

    /**
     * move attributeorder down
     *
     *
     * @param array $params
     */
    public function moveDown( $params )
    {
        $this->validate_params( $params );

        $this->query("SELECT geocontexter.gc_attribute_move_order_down('{$params['id_attribute']}', '{$params['id_group']}')");
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_attribute'])) {
            throw new \Exception('id_attribute field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_attribute']))  {
            throw new \Exception('id_attribute isnt from type bigint');
        }

        if (!isset($params['id_group'])) {
            throw new \Exception('id_group field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_group'])) {
            throw new \Exception('id_group isnt from type bigint');
        }
    }
}
