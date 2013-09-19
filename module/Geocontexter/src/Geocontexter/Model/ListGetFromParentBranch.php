<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get list parent branch from id_list
 *
   USAGE:
   <pre>

   $ListGetFromParentBranch = $this->CoreModel('ListGetFromParentBranch');

   $params  = array('id_list' => bigint id_list);

   $result  = $ListGetFromParentBranch->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = & $result;
   }

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

class ListGetFromParentBranch extends    AbstractModel
                              implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            if ($params['id_list'] == 0) {
                return array();
            }

            $sql = 'SELECT * FROM geocontexter.gc_list_get_branch(?)';

            return $this->query($sql, array($params['id_list']));

        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( $params )
    {
        if (!isset($params['id_list'])) {
            throw new \Exception('id_list field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_list'])) {
            throw new \Exception('id_list isnt from type bigint');
        }
    }
}