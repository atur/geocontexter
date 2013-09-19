<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new attribute group
 *
 *  USAGE:
   <pre>
   $attribute_group_add = $this->CoreModel('AttributeAddGroup');

   $params  = array('id_group'    => bigint,
                    'title'       => string, // required
                    'description' => string,
                    'update_time' => string,
                    'id_table'    => int,    // required
                    'id_status'   => int,
                    'lang'        => string,
                    'id_owner'    => bigint); // required

   $attribute_group_add->run( $params );

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

class AttributeAddGroup extends    AbstractModel
                        implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_group'    => true,
                                    'id_status'   => true,
                                    'title'       => true,
                                    'description' => true,
                                    'update_time' => true,
                                    'lang'        => true,
                                    'id_table'    => true,
                                    'id_owner'    => true
                                    );

    /**
     * add attribute group
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params( $params );

            if (!isset($params['update_time'])) {
                $params['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
            }

            $this->insert('gc_attribute_group', 'geocontexter', $params);

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

        if (!isset($params['title'])) {
            throw new \Exception('Context title field isnt defined');
        }

        if (empty($params['title'])) {
            throw new \Exception('Context title is empty');
        }

        if (isset($params['lang'])) {
            if (empty($params['lang'])) {
                throw new \Exception('lang is empty');
            }
        }

        if (!isset($params['id_table'])) {
            throw new \Exception('Attribute group related id_table field isnt defined');
        }

        if (false === is_int($params['id_table'])) {
            throw new \Exception('id_table isnt from type integer');
        }

        if (isset($params['id_status'])) {
            if (false === is_int($params['id_status'])) {
                throw new \Exception('Attribute group id_status isnt from type int');
            }
        }

        if (isset($params['update_time'])) {
            $val_date = new \Zend\Validator\Date(array('format' => 'Y-m-d H:i:s'));

            if (true !== $val_date->isValid($params['update_time'])) {
                throw new \Exception('update_time has wrong format: ' . $params['update_time']);
            }
        }
    }
}
