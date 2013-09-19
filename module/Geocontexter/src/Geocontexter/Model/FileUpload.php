<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Upload file in folder
 *
   USAGE:
   <pre>
   $file = new Geocontexter_Model_FileUpload;

   $params  = array('post_name'   => string,  // as defined in $_FILES
                    'data_folder' => string   // full path to the image directory
                    );

   $result  = $file->upload( $params );

   if($result instanceof Mozend_ModelError)
   {
       return $this->error( $result->getErrorString(), __file__, __line__ );
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 768 $ / $LastChangedDate: 2010-12-16 16:11:56 +0100 (jeu., 16 déc. 2010) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Zend\Form\Element;
use Zend\Form\Form;

class FileUpload extends Form
{
    /**
     * upload image
     */
    public function upload( $params )
    {
        try
        {
            $this->adapter = new Zend_File_Transfer_Adapter_Http( array('ignoreNoFile' => true) );

            $this->adapter->addValidator('Size', false, 5000000);

            $this->adapter->setValidators(array('ExcludeExtension' => array('php', 'exe')));

            $this->validate_params( $params );

            $this->path  = $params['data_folder'];
            $this->_file = $this->file_info[$params['post_name']];

            // test if image upload
            //
            if (empty($this->file_info[$params['post_name']]['tmp_name']) || empty($this->file_info[$params['post_name']]['name'])) {
                throw new \Exception('Upload file failed. Uploaded file dosent exists in $_FILES');
            }

            $this->create_files_folder( $params['data_folder'] );

            $this->adapter->setDestination( $params['data_folder'] );

            if (!$this->adapter->receive()) {
                throw new \Exception($this->adapter->getMessages());
                throw new \Exception('File upload error');
            } else {
                return $this->file_info[$params['post_name']];
            }

        } catch(\Exception $e) {
            $this->removeFile();
            throw $e;
        }
    }

    /**
     * validate parameters
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['post_name'])) {
            throw new \Exception('post_name field isnt defined');
        }

        if (false === is_string($params['post_name']))  {
            throw new \Exception('post_name isnt from type string');
        }

        $this->file_info = $this->adapter->getFileInfo();

        if (!isset($this->file_info[$params['post_name']]))  {
            throw new \Exception('post_name isnt defined in $_FILES array');
        }

        if (!isset($params['data_folder'])) {
            throw new \Exception('data_folder isnt defined');
        }
    }

    /**
     *
     */
    private function create_files_folder( $path )
    {
        if (!is_dir($path))  {
            $oldumask = umask(0);

            if (false === mkdir($path, 0777)) {
                umask($oldumask);
                throw new \Exception('Couldnt create directory: ' . $path);
            }

            umask($oldumask);
        }
    }

    /**
     *
     */
    public function removeFile()
    {
        if (isset($this->path)) {
            if (file_exists( $this->path . '/' . $this->_file['name'] )) {
                if (false === unlink($this->path . '/' . $this->_file['name'])) {
                    throw new \Exception('Error: Couldnt remove file: ' . $this->path . '/' . $this->_file['name']);
                }
            }
        }
    }
}