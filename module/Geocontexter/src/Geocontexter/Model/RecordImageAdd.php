<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geoLister/
 * @package GeoContexter
 */

/**
 * Add new record image entry in db table geocontexter.gc_record_image
 *
 *  USAGE:
   <pre>

   $RecordImageAdd = $this->CoreModel('RecordImageAdd');

   $params  = array('id_record'   => bigint,
                    'title'       => string,
                    'description' => string,
                    'id_status'   => true,
                    'file_mime'   => string,
                    'file_name'   => string,
                    'file_size'   => string,
                    'file_height' => string,
                    'file_width'  => string
                   );

   $result = $RecordImageAdd->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
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

class RecordImageAdd extends    AbstractModel
                     implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_record'          => true,
                                    'title'              => true,
                                    'description'        => true,
                                    'id_status'          => true,
                                    'file_mime'          => true,
                                    'file_name'          => true,
                                    'file_size'          => true,
                                    'file_height'        => true,
                                    'file_width'         => true
                                    );

    /**
     * add image data
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            if (!isset($params['update_time'])) {
                $params['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
            }

            if (!isset($params['id_status'])) {
                $params['id_status'] = 200;
            }

            $params['preferred_order'] = new \Zend\Db\Sql\Expression("geocontexter.gc_get_new_entry_order('gc_record_image',
                                                                                                          'id_record',
                                                                                                          {$params['id_record']})");

            $this->insert('geocontexter', 'geocontexter', $params);

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
        foreach ($params as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (!isset($params['id_record'])) {
            throw new \Exception('id_record field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_record'])) {
            throw new \Exception('id_record isnt from type bigint');
        }

        if (isset($params['update_time'])) {

            $val_date = new \Zend\Validator\Date('yyyy-MM-dd HH:mm:ss');

            if (true !== $val_date->isValid($params['update_time'])) {
                throw new \Exception('update_time has wrong format: ' . $params['update_time']);
            }
        }
    }
}
