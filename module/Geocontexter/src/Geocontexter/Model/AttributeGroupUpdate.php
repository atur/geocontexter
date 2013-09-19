<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Update attribute group
 *
 *  USAGE:
   <pre>
    $attribute_group_update = $this->CoreModel('AttributeGroupUpdate');

    //    Idetification number (id_table) of tables
    //    1 = gc_record
    //    2 = gc_list
    //    3 = gc_item
    //    4 = gc_keyword
    //    5 = gc_context
    //    6 = gc_user
    //    7 = gc_project
    //    8 = gc_overlay

    $params  = array('id_group' => bigint id_group,
                     'data'     => array('title'       => string,
                                         'description' => string,
                                         'id_status'   => int,
                                         'id_table'    => int,
                                         'lang'        => string));

    $attribute_group_update->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeGroupUpdate extends   AbstractModel
                                     implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('title'       => true,
                                    'id_status'   => true,
                                    'description' => true,
                                    'id_table'    => true,
                                    'lang'        => true
                                    );

    /**
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $params['data']['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");

            $this->update('gc_attribute_group','geocontexter', $params['data'], array('id_group' => $params['id_group']));

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
        if (!isset($params['data'])) {
            throw new \Exception('data array isnt defined');
        }

        foreach ($params['data'] as $key => $val) {
            if(!isset($this->allowed_fields[$key]))
            {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (isset($params['data']['title'])) {
            if (empty($params['data']['title'])) {
                throw new \Exception('Group title is empty');
            }
        }

        if (!isset($params['id_group'])) {
            throw new \Exception('id_group field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_group'])) {
            throw new \Exception('id_group isnt from type bigint');
        }

        if (isset($params['id_status'])) {
            $val_int = new \Zend\Validator\Int();

            if (false === $val_int->isValid($params['id_status'])) {
                throw new \Exception('Attribute group id_status isnt from type int');
            }
        }


    }
}