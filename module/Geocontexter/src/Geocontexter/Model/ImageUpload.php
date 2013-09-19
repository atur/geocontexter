<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Upload image in folder and create thumbnail from
 *
   USAGE:
   <pre>
   $image = new Geocontexter_Model_ImageUpload;

   $params  = array('post_name'   => string,  // as defined in $_FILES
                    'data_folder' => string   // full path to the image directory
                    );

   $result  = $image->upload( $params );

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

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ImageUpload extends    AbstractModel
                  implements InterfaceModel
{
    /**
     * upload image
     */
    public function run( $params )
    {
        try {
            $this->upload($params['request'], $target, $params['post_name'], $params['form_name']);


            $this->adapter = new Zend_File_Transfer_Adapter_Http( array('ignoreNoFile' => true) );

            $this->adapter->addValidator('Size', false, 1000000);

            $this->adapter->addValidator('ImageSize', false,
                                      array('minwidth'  => 100,
                                            'maxwidth'  => 2000,
                                            'minheight' => 100,
                                            'maxheight' => 2000)
                                  );

            $this->adapter->setValidators(array('Extension' => array('jpg', 'jpeg', 'gif', 'png')));

            $this->set_params( $params );

            $this->path  = $params['data_folder'];
            $this->_file = $this->file_info[$params['post_name']];

            // test if image upload
            //
            if (empty($this->file_info[$params['post_name']]['tmp_name']) || empty($this->file_info[$params['post_name']]['name'])) {
                throw new \Exception('Upload image failed. Uploaded file dosent exists in $_FILES');
            }

            $this->create_files_folder( $params['data_folder'] );

            $this->adapter->setDestination( $params['data_folder'] );

            if (!$this->adapter->receive()) {
                throw new \Exception($this->adapter->getMessages());
                throw new \Exception('File upload error');
            } else {
                $this->build_thumbnail($params['data_folder'], $this->file_info[$params['post_name']]);

                return $this->file_info[$params['post_name']];
            }
        } catch(Exception $e) {
            $this->removeFile();

            return new Mozend_ModelError( $this->get_error() );
        }
    }

    /**
     * upload lists backup file for import
     */
    public function _upload($request, $target, $file_input_name, $form_name)
    {
        $upload = $this->getServiceLocator()->get('CoreUploadForm');
        $upload->init($target, $file_input_name, $form_name);

        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $upload->form->setData($post);
            if ($upload->form->isValid()) {

                $data = $upload->form->getData();

                $ListImport = $this->CoreModel('ListImport');

                $ListImport->run(array('file' => $data['upload_file']));
            }
        }
    }

    /**
     * validate parameters
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if(!isset($params['post_name']))
        {
            throw new \Exception('post_name field isnt defined');
        }

        if(false === is_string($params['post_name']))
        {
            throw new \Exception('post_name isnt from type string');
        }

        $this->file_info = $this->adapter->getFileInfo();

        if(!isset($this->file_info[$params['post_name']]))
        {
            throw new \Exception('post_name isnt defined in $_FILES array');
        }

        if(!isset($params['data_folder']))
        {
            throw new \Exception('data_folder isnt defined');
        }
    }

    /**
     *
     */
    private function create_files_folder( $path )
    {
        if(!is_dir($path))
        {
            $oldumask = umask(0);

            if(false === mkdir($path, 0777))
            {
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
        if(isset($this->path))
        {
            if(file_exists( $this->path . '/' . $this->_file['name'] ))
            {
                if(false === unlink($this->path . '/' . $this->_file['name']))
                {
                    throw new \Exception('Error: Couldnt remove file: ' . $this->path . '/' . $this->_file['name']);
                }
            }

            if(file_exists( $this->path . '/thumb-' . $this->_file['name'] ))
            {
                if(false === unlink($this->path . '/thumb-' . $this->_file['name']))
                {
                    throw new \Exception('Error: Couldnt remove file: ' . $this->path . '/thumb-' . $this->_file['name']);
                }
            }
        }
    }

    /**
     *
     */
    private function build_thumbnail($path, & $file_info)
    {
        $image = getimagesize ( $path . '/' . $file_info['name'] );

        if(!isset($image[0]) || !isset($image[1]))
        {
            throw new \Exception('Couldnt fetch image info: ' . var_export($image,true));
        }

        $file_info['image_width']  = $image[0];
        $file_info['image_height'] = $image[1];

        $newwidth = 140;
        $newheight = (int) ((140 / $image[0]) * $image[1]);

        if(false === ($thumb = imagecreatetruecolor($newwidth, $newheight)))
        {
            throw new \Exception('imagecreatetruecolor failed with params: ' . var_export(array($newwidth, $newheight),true));
        }

        switch(strtolower($file_info['type']))
        {
            case 'image/jpeg':
                if(false === ($source = imagecreatefromjpeg($path . '/' . $file_info['name'])))
                {
                    throw new \Exception('imagecreatefromjpeg failed with param: ' . var_export($path . '/' . $file_info['name'],true));
                }

                // Resize
                if(false === (imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $image[0], $image[1])))
                {
                    $_params = array($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $image[0], $image[1]);
                    throw new \Exception('imagecopyresized failed with params: ' . var_export($_params,true));
                }

                if(false === (imagejpeg($thumb, $path . '/thumb-' . $file_info['name'])))
                {
                    $_params = array($thumb, $path . '/thumb-' . $file_info['name']);
                    throw new \Exception('imagejpeg failed with params: ' . var_export($_params,true));
                }

                return;

            case 'image/gif':
                if(false === ($source = imagecreatefromgif($path . '/' . $file_info['name'])))
                {
                    throw new \Exception('imagecreatefromgif failed with param: ' . var_export($path . '/' . $file_info['name'],true));
                }

                // Resize
                if(false === (imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $image[0], $image[1])))
                {
                    $_params = array($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $image[0], $image[1]);
                    throw new \Exception('imagecopyresized failed with params: ' . var_export($_params,true));
                }

                if(false === (imagegif($thumb, $path . '/thumb-' . $file_info['name'])))
                {
                    $_params = array($thumb, $path . '/thumb-' . $file_info['name']);
                    throw new \Exception('imagegif failed with params: ' . var_export($_params,true));
                }

                return;

            case 'image/png':
                if(false === ($source = imagecreatefrompng($path . '/' . $file_info['name'])))
                {
                    throw new \Exception('imagecreatefrompng failed with param: ' . var_export($path . '/' . $file_info['name'],true));
                }

                // Resize
                if(false === (imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $image[0], $image[1])))
                {
                    $_params = array($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $image[0], $image[1]);
                    throw new \Exception('imagecopyresized failed with params: ' . var_export($_params,true));
                }

                if(false === (imagepng($thumb, $path . '/thumb-' . $file_info['name'])))
                {
                    $_params = array($thumb, $path . '/thumb-' . $file_info['name']);
                    throw new \Exception('imagepng failed with params: ' . var_export($_params,true));
                }

                return;
            default:
                    throw new \Exception('unknown image type: ' . var_export($file_info,true));
        }
    }
}