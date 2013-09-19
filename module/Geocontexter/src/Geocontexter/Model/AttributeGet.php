<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get attribute
 *
 *  USAGE:
   <pre>

    $AttributeGet = $this->CoreModel('AttributeGet');

    $params = array('id_attribute'  => bigint,
                    'fetch_assoc'   => bool,
                    // 'system_serial' field is included in the result
                    // it contains the system serial if id_group is within the system serial
                    // else null
                    'system_serial' => bool); // optional , value: true


    $this->view->result = $AttributeGet->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeGet extends    AbstractModel
                   implements InterfaceModel
{
    /**
     * get item attribut
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

        if ($params['system_serial'] == true) {
            $_system_serial = ", geocontexter.gc_system_is_serial(id_group) AS system_serial";
        }

        $sql = 'SELECT  * '.$_system_serial.'
                FROM  geocontexter.gc_attribute
                WHERE id_attribute=?';

        $result = $this->query($sql, array($params['id_attribute']), true);

        return $result->current();
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
            throw new \Exception('id_attribute isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_attribute'])) {
            throw new \Exception('id_attribute isnt from type bigint');
        }
    }
}
