<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Delete record image
 *
 *  USAGE:
   <pre>

    $RecordImageDelete = $this->CoreModel('RecordImageDelete');

    $result  = $RecordImageDelete->run( array('id_image'     => bigint,
                                              'files_folder' => string) );

    if ($result instanceof \Core\Library\Exception) {
        return $this->error( $result->getMessage(), __file__, __line__);
    }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 732 $ / $LastChangedDate: 2010-11-04 18:16:50 +0100 (jeu., 04 nov. 2010) $ / $LastChangedBy: armand.turpel $
  \*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class RecordImageDelete extends    AbstractModel
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

            $RecordImageGet = $this->CoreModel('RecordImageGet');

            $img_params  = array('id_image' => $params['id_image']);

            $image = $RecordImageGet->run( $img_params );

            if ($image instanceof \Core\Library\Exception) {
               throw new \Exception( $image->getMessage());
            }

            $this->delete('gc_record_image', 'geocontexter', array('id_image' => $params['id_image']));

            if (false === unlink(DATA_PATH . '/gc_record/' . $params['files_folder'] . '/' . $image['file_name'])) {
                throw Exception('Cant unlink file: ' . DATA_PATH . '/gc_record/' . $params['files_folder'] . '/' . $image['file_name']);
            } else {
                @unlink(DATA_PATH . '/gc_record/' . $params['files_folder'] . '/thumb-' . $image['file_name']);
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
        if (!isset($params['id_image'])) {
            throw new \Exception('id_image field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_image'])) {
            throw new \Exception('id_image isnt from type bigint');
        }

        if (!isset($params['files_folder'])) {
            throw new \Exception('files_folder field isnt defined');
        }

        if (!is_string($params['files_folder'])) {
            throw new \Exception('files_folder field isnt from type string');
        }
    }
}
