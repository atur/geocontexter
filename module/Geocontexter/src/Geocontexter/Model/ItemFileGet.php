<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get item file entry from db
 *
 *  USAGE:
    <pre>

    $ItemFileGet = $this->CoreModel('ItemFileGet');

    $params  = array('id_file' => bigint);

    $files = $ItemFileGet->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 768 $ / $LastChangedDate: 2010-12-16 16:11:56 +0100 (jeu., 16 déc. 2010) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ItemFileGet extends    AbstractModel
                  implements InterfaceModel
{
    /**
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
            $_system_serial = ", geocontexter.gc_system_is_serial(id_file) AS system_serial";
        }

        $sql = 'SELECT  * ' . $_system_serial . '
                FROM  geocontexter.gc_item_file
                WHERE id_file = ' . $params['id_file'];

        $result $this->query($sql);

        if (isset($result[0])) {
            return $result[0];
        }

        return array();
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_file'])) {
            throw new \Exception('id_file field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_file'])) {
            throw new \Exception('id_file isnt from type bigint');
        }
    }
}
