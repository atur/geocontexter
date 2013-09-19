<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geoLister/
 * @package GeoContexter
 */

/**
 * get all item files
 *
 *  USAGE:
    <pre>

     $ItemFileGetAll = $this->CoreModel('ItemFileGetAll');

     $params  = array('id_item'   => bigint,
                      'id_status' => int);

     $files = $ItemFileGetAll->run( $params );


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

class ItemFileGetAll extends AbstractModel
                     implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_item'   => true,
                                    'id_status' => true);

    /**
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
            $_system_serial = ", geocontexter.gc_system_is_serial(id_file) AS system_serial";
        }

        $_status = "";
        if (isset($params['id_status'])) {
            $_status = 'AND id_status = ' . $params['id_status'];
        }

        $sql = 'SELECT  * ' . $_system_serial . '
                FROM  geocontexter.gc_item_file
                WHERE id_item = ' . $params['id_item'] . '
                ' . $_status . '
                ORDER BY preferred_order
                ';

        return $this->query($sql);
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

        if (isset($params['id_status'])) {
            if (false === is_int($params['id_status'])) {
                throw new \Exception('id_status isnt from type int');
            }
        }
    }
}
