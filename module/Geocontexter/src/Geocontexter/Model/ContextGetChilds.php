<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get contextes from id_parent
 *
   USAGE:
   <pre>
    $context_get_childs = $this->CoreModel('ContextGetChilds');

    $params  = array('id_parent' => bigint,
                     // 'system_serial' field is included in the result
                     // it contains the system serial if id_group is within the system serial
                     // else null
                     'system_serial' => bool); // optional , value: true

    $this->view->result = $context_get_childs->run( $params );

   // Each result set contains an additional var 'num_childs':
   // the number of contextes which have the current context as parent
   //

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

class ContextGetChilds extends    AbstractModel
                       implements InterfaceModel
{
    /**
     * get contextes from id_parent
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        // if the system_serial check must be included
        //
        $_system_serial = "";
        if (isset($params['system_serial']) && ($params['system_serial'] == true)) {
            $_system_serial = ", geocontexter.gc_system_is_serial(id_context) AS system_serial";
        }

        $sql = 'SELECT  grc.* '.$_system_serial.',
                        (SELECT count(id_context)
                         FROM geocontexter.gc_context
                         WHERE id_parent = grc.id_context) AS num_childs
                FROM  geocontexter.gc_context AS grc
                WHERE grc.id_parent = ?
                ORDER BY grc.title';

        return $this->query($sql, array($params['id_parent']));
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        $val_digits = new \Zend\Validator\Digits();

        if (!isset($params['id_parent'])) {
            throw new \Exception('id_parent field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_parent'], 'Digits')) {
            throw new \Exception('id_parent isnt from type bigint');
        }
    }
}