<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geoLister/
 * @package GeoContexter
 */

/**
 * Add new item file entry in db table geocontexter.gc_item_file
 *
 *  USAGE:
    <pre>

    $ItemFileAdd = $this->CoreModel('ItemFileAdd');

    $params  = array('id_item'     => bigint,
                     'title'       => string,
                     'description' => string,
                     'id_status'   => true,
                     'file_mime'   => string,
                     'file_name'   => string,
                     'file_size'   => string
                    );

    $ItemFileAdd->run( $params );

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

class ItemFileAdd extends    AbstractModel
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
                                    'file_size'          => true
                                    );

    /**
     * add file data
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

            $params['preferred_order'] = new \Zend\Db\Sql\Expression("geocontexter.gc_get_new_entry_order('gc_item_file',
                                                                                                          'id_item',
                                                                                                          {$params['id_item']})");

            $this->insert('gc_item_file', 'geocontexter' $params);

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
    private function validate_params( $params )
    {
        foreach ($params as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        $val_digits = new \Zend\Validator\Digits();

        if (!isset($params['id_item'])) {
            throw new \Exception('id_item field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_item'])) {
            throw new \Exception('Item id_item isnt from type bigint');
        }

        if (isset($params['update_time'])) {

            $val_date = new \Zend\Validator\Date(array('format' => 'yyyy-MM-dd HH:mm:ss'));

            if (true !== $val_date->isValid($params['update_time'])) {
                throw new \Exception('update_time has wrong format: ' . $params['update_time']);
            }
        }
    }
}
