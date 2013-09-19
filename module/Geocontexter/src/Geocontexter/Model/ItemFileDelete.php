<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Delete item image
 *
 *  USAGE:
    <pre>

    $ItemFileDelete = $this->CoreModel('ItemFileDelete');

    $ItemFileDelete->run( array('id_file'      => bigint,
                                'files_folder' => string) );


   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 732 $ / $LastChangedDate: 2010-11-04 18:16:50 +0100 (jeu., 04 nov. 2010) $ / $LastChangedBy: armand.turpel $
 */


namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ItemFileDelete extends    AbstractModel
                     implements InterfaceModel
{
    /**
     *
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $ItemFileGet = $this->CoreModel('ItemFileGet');

            $file_params  = array('id_file' => $params['id_file']);

            $file = $ItemFileGet->run( $file_params);

            $this->delete('gc_item_file', 'geocontexter', array('id_file = ' . $params['id_file']));

            if (false === unlink(DATA_PATH . '/gc_item/' . $params['files_folder'] . '/' . $file['file_name'])) {
                throw new \Exception('Cant unlink file: ' . DATA_PATH . '/gc_item/' . $params['files_folder'] . '/' . $file['file_name']);
            }

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
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
        if (!isset($params['id_file'])) {
            throw new \Exception('id_file field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_file'])) {
            throw new \Exception('id_file isnt from type bigint');
        }

        if (!isset($params['files_folder'])) {
            throw new \Exception('files_folder field isnt defined');
        }

        if (!is_string($params['files_folder'])) {
            throw new \Exception('files_folder field isnt from type string');
        }
    }
}
