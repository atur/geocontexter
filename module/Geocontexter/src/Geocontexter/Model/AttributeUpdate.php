<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geoLister/
 * @package GeoContexter
 */

/**
 * Update attribute definition
 *
 *  USAGE:
   <pre>
    $attribute_update = $this->CoreModel('AttributeUpdate');

    $params = array('id_attribute' => bigint);

    $params['data'] = array('attribute_name'          => string,
                             'attribute_title'         => string,
                             'attribute_description'   => string,
                             'attribute_type'          => string,
                             'attribute_regex'         => string,
                             'attribute_html_type'     => string,
                             'default_display'         => bool,
                             'multi_value'             => bool,
                             'lang'                    => string);

    $attribute_update->run( $params );

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

class AttributeUpdate extends AbstractModel
                              implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('attribute_name'          => true,
                                    'attribute_title'         => true,
                                    'attribute_description'   => true,
                                    'attribute_type'          => true,
                                    'attribute_required'      => true,
                                    'attribute_regex'         => true,
                                    'attribute_html_type'     => true,
                                    'id_status'               => true,
                                    'default_display'         => true,
                                    'multi_value'             => true
                                    );

    /**
     * Update item attribute
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        $params['data']['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");

        $this->update('gc_attribute','geocontexter',
                      $params['data'],
                      array('id_attribute' => $params['id_attribute']));
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
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (!isset($params['data']['attribute_name'])) {
            throw new \Exception('attribute_name field isnt defined');
        }

        if (empty($params['data']['attribute_name'])) {
            throw new \Exception('attribute_name is empty');
        }

        if (!isset($params['data']['attribute_type'])) {
            throw new \Exception('attribute_type field isnt defined');
        }

        if (empty($params['data']['attribute_type'])) {
            throw new \Exception('attribute_type is empty');
        }

        if (isset($params['data']['attribute_required'])) {
            if (false === is_bool($params['attribute_required'])) {
                throw new \Exception('attribute_required isnt from type bool');
            }
        }

        if (!isset($params['id_attribute'])) {
            throw new \Exception('id_attribute field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_attribute'])) {
            throw new \Exception('id_attribute isnt from type bigint');
        }


    }
}
