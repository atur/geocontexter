<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Move keyword preferred order
 *
   USAGE:
   <pre>

   $KeywordMoveOrder = $this->CoreModel('KeywordMoveOrder');

   $params  = array('id_keyword' => bigint);

   $result  = $KeywordMoveOrder->moveUp( $params );
   // or
   // $result  = $KeywordMoveOrder->moveDown( $params );

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

class KeywordMoveOrder extends AbstractModel
{
    /**
     * move keywordorder up
     *
     *
     * @param array $params
     */
    public function moveUp( $params )
    {
        try {

            $this->validate_params($params);

            $this->query("SELECT geocontexter.gc_keyword_move_order_up({$params['id_keyword']})");

        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * move keywordorder down
     *
     *
     * @param array $params
     */
    public function moveDown( $params )
    {
        try {

            $this->validate_params($params);

            $this->query("SELECT geocontexter.gc_keyword_move_order_down({$params['id_keyword']})");

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
        if (!isset($params['id_keyword'])) {
            throw new \Exception('id_keyword field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_keyword'])) {
            throw new \Exception('id_keyword isnt from type bigint');
        }

        
    }
}
