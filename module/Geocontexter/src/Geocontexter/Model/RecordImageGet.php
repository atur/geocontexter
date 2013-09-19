<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geoLister/
 * @package GeoContexter
 */

/**
 * get record image entry from db
 *
 *  USAGE:
    <pre>

     $RecordImageGet = $this->CoreModel('RecordImageGet');

    $params  = array('id_image' => bigint);

    $images = $RecordImageGet->run( $params );

    if ($images instanceof \Core\Library\Exception) {
        return $this->error( $images->getMessage(), __file__, __line__);
    } else {
       $this->view->result = $images;
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

class RecordImageGet extends    AbstractModel
                     implements InterfaceModel
{
    /**
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
                $_system_serial = ", geocontexter.gc_system_is_serial(id_image) AS system_serial";
            }

            $sql = 'SELECT  * ' . $_system_serial . '
                    FROM  geocontexter.gc_record_image
                    WHERE id_image = ?';

            $result = $this->query($sql, array($params['id_image']));
            return $result[0];

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
        if (!isset($params['id_image'])) {
            throw new \Exception('id_image field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_image'])) {
            throw new \Exception('id_image isnt from type bigint');
        }
    }
}
