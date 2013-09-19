<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Move record image order
 *
 *  USAGE:
   <pre>

   $RecordImageMoveOrder = $this->CoreModel('RecordImageMoveOrder');

   $params  = array('id_record' => bigint,
                    'id_image'  => bigint);

   $result  = $RecordImageMoveOrder->moveUp( $params );
   // or
   // $result  = $RecordImageMoveOrder->moveDown( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 768 $ / $LastChangedDate: 2010-12-16 16:11:56 +0100 (jeu., 16 déc. 2010) $ / $LastChangedBy: armand.turpel $
  \*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class RecordImageMoveOrder extends AbstractModel
{
    /**
     * move image order up
     *
     *
     * @param array $params
     */
    public function moveUp( $params )
    {
        try {

            $this->validate_params($params);

            $this->query("SELECT geocontexter.gc_record_image_move_order_up('{$params['id_image']}','{$params['id_record']}')");

        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * move image order down
     *
     *
     * @param array $params
     */
    public function moveDown( $params )
    {
        try {

            $this->validate_params($params);

            $this->query("SELECT geocontexter.gc_record_image_move_order_down('{$params['id_image']}','{$params['id_record']}')");

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
        if (!isset($params['id_record'])) {
            throw new \Exception('id_record field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_record'])) {
            throw new \Exception('id_record isnt from type bigint');
        }

        if (!isset($params['id_image'])) {
            throw new \Exception('id_image field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_image'])) {
            throw new \Exception('id_image isnt from type bigint');
        }
    }
}
