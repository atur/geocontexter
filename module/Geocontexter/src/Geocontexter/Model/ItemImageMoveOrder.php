<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Move item image order
 *
 *  USAGE:
   <pre>

    $ItemImageMoveOrder = $this->CoreModel('ItemImageMoveOrder');

    $params  = array('id_item'  => bigint,
                     'id_image' => bigint);

    $ItemImageMoveOrder->moveUp( $params );
    // or
    // $ItemImageMoveOrder->moveDown( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 768 $ / $LastChangedDate: 2010-12-16 16:11:56 +0100 (jeu., 16 déc. 2010) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class ItemImageMoveOrder extends AbstractModel
{
    /**
     * move image order up
     *
     *
     * @param array $params
     */
    public function moveUp( $params )
    {
        $this->validate_params($params);

        $this->query("SELECT geocontexter.gc_item_image_move_order_up('{$params['id_image']}','{$params['id_item']}')");
    }

    /**
     * move image order down
     *
     *
     * @param array $params
     */
    public function moveDown( $params )
    {
        $this->validate_params($params);

        $this->db->query("SELECT geocontexter.gc_item_image_move_order_down('{$params['id_image']}','{$params['id_item']}')");
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

        if (!isset($params['id_image'])) {
            throw new \Exception('id_image field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_image'])) {
            throw new \Exception('id_image isnt from type bigint');
        }
    }
}
