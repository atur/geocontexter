<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get context parent branch from id_context
 *
   USAGE:
   <pre>
   $context_get_from_parent_branch = $this->CoreModel('ContextGetFromParentBranch');

   $params  = array('id_context' => bigint id_context);

   $this->view->result  = $context_get_from_parent_branch->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 834 $ / $LastChangedDate: 2011-03-04 16:40:00 +0100 (Fr, 04 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ContextGetFromParentBranch extends    AbstractModel
                                 implements InterfaceModel
{
    /**
     * get context parent branch from id_context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        if ($params['id_context'] == 0) {
            return array();
        }

        $sql = 'SELECT * FROM geocontexter.gc_context_get_branch(?)';

        return $this->query($sql, array($params['id_context']));
    }

    /**
     * validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        $val_digits = new \Zend\Validator\Digits();

        if (!isset($params['id_context'])) {
            throw new \Exception('id_context field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_context'])) {
            throw new \Exception('id_context isnt from type bigint');
        }
    }
}