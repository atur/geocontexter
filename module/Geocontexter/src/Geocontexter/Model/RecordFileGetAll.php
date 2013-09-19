<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geoLister/
 * @package GeoContexter
 */

/**
 * get all record files
 *
 *  USAGE:
    <pre>

    $RecordFileGetAll = $this->CoreModel('RecordFileGetAll');

    $params  = array('id_record' => bigint,
                     'id_status' => int);

    $files = $RecordFileGetAll->run( $params );

    if ($files instanceof \Core\Library\Exception) {
        return $this->error( $files->getMessage(), __file__, __line__);
    } else {
       $this->view->result = $files;
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
use Core\Model\InterfaceModel;

class RecordFileGetAll extends    AbstractModel
                       implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_record' => true,
                                    'id_status' => true);

    /**
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

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
                    FROM  geocontexter.gc_record_file
                    WHERE id_record = ' . $params['id_record'] . '
                    ' . $_status . '
                    ORDER BY preferred_order
                    ';

            return $this->query($sql);

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

        if (false === $val_digits->isValid($params['id_record']))  {
            throw new \Exception('id_record isnt from type bigint');
        }

        if (isset($params['id_status'])) {

            $val_int = new \Zend\Validator\Int();

            if (false === $val_int->isValid($params['id_status'])) {
                throw new \Exception('id_status isnt from type int');
            }
        }
    }
}
