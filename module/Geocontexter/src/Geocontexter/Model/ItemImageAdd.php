<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new item image entry in db table geocontexter.gc_item_image
 *
 *  USAGE:
   <pre>

    $ItemImageAdd = $this->CoreModel('ItemImageAdd');

    $params  = array('id_item'     => bigint,
                     'title'       => string,
                     'description' => string,
                     'id_status'   => int,
                     'file_mime'   => string,
                     'file_name'   => string,
                     'file_size'   => string,
                     'file_height' => string,
                     'file_width'  => string
                    );

    $ItemImageAdd->run( $params );

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

class ItemImageAdd extends AbstractModel
                           implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_item'            => true,
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
        $this->validate_params($params);

        if (!isset($params['update_time'])) {
            $params['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
        }

        if (!isset($params['id_status'])) {
            $params['id_status'] = 200;
        }

        $params['preferred_order'] = new \Zend\Db\Sql\Expression("geocontexter.gc_get_new_entry_order('gc_item_image',
                                                                                           'id_item',
                                                                                           {$params['id_item']})");

        $this->insert('gc_item_image', 'geocontexter', $params);
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

        if (!isset($params['id_item'])) {
            throw new \Exception('id_item field isnt defined');
        }

        $val_date = new \Zend\Validator\Date('yyyy-MM-dd HH:mm:ss');

        if (isset($params['update_time'])) {
            if (true !== $val_date->isValid($params['update_time'])) {
                throw new \Exception('update_time has wrong format: ' . $params['update_time']);
            }
        }
    }
}
