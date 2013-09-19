<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * delete item
 *
   USAGE:
   <pre>

   $ItemDelete = $this->CoreModel('ItemDelete');

   $params  = array('id_item' => bigint);

   $ItemDelete->run( $params );

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

class ItemDelete extends    AbstractModel
                 implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        $this->delete('gc_item','geocontexter', array('id_item' => $params['id_item']));
    }

    /**
     * set and validate parameters
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
    }
}
