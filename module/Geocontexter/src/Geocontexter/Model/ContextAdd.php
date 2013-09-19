<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new context
 *
   USAGE:
   <pre>
    $context_add = $this->CoreModel('ContextAdd');

    $params  = array('title'       => string,
                     'description' => string,
                     'id_parent'   => bigint,
                     'id_owner'    => bigint,
                     'update_time' => string,
                     'id_status'   => smallint);

    $context_add->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ContextAdd extends    AbstractModel
                 implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('title'       => true,
                                    'description' => true,
                                    'id_parent'   => true,
                                    'id_status'   => true,
                                    'update_time' => true,
                                    'lang'        => true
                                    );

    /**
     * add context
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

        $this->insert('gc_context', 'geocontexter', $params);
    }

    /**
     * set and validate parameters
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        foreach ($params as $key => $val)
        {
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

        $val_digits = new \Zend\Validator\Digits();

        if (isset($params['id_parent'])) {
            if (false === $val_digits->isValid($params['id_parent'])) {
                throw new \Exception('id_parent isnt from type bigint');
            }
        }

        $val_date = new \Zend\Validator\Date(array('format' => 'yyyy-MM-dd HH:mm:ss'));

        if (isset($params['update_time'])) {
            if (true !== $val_date->isValid($params['update_time'])) {
                throw new \Exception('update_time has wrong format: ' . $params['update_time']);
            }
        }


    }
}