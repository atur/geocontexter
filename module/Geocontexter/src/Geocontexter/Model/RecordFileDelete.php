<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Delete record file
 *
 *  USAGE:
   <pre>

   $RecordFileDelete = $this->CoreModel('RecordFileDelete');

   $result  = $RecordFileDelete->run( array('id_file'      => bigint,
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

class RecordFileDelete extends    AbstractModel
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

            $RecordFileGet = $this->CoreModel('RecordFileGet');

            $file_params  = array('id_file' => $params['id_file']);

            $file = $RecordFileGet->run( $file_params);

            if ($file instanceof \Core\Library\Exception) {
               throw new \Exception($file->getMessage());
            }

            $this->delete('gc_record_file', 'geocontexter', array('id_file' => $params['id_file']));

            if (false === unlink(DATA_PATH . '/gc_record/' . $params['files_folder'] . '/' . $file['file_name'])) {
                throw new \Exception('Cant unlink file: ' . DATA_PATH . '/gc_record/' . $params['files_folder'] . '/' . $file['file_name']);
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
