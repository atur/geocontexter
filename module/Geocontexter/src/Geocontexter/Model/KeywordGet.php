<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get keyword from id_keyword
 *
   USAGE:
   <pre>

   $KeywordGet = $this->CoreModel('KeywordGet');

   $params  = array('id_keyword' => bigint,
                    // 'system_serial' field is included in the result
                    // it contains the system serial if id_group is within the system serial
                    // else null
                   'system_serial' => bool);// optional , value: true

   $result  = $KeywordGet->run( $params,);

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
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class KeywordGet extends    AbstractModel
                 implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            // if the system_serial check must be included
            //
            $_system_serial = "";
            if (isset($params['system_serial']) && ($params['system_serial'] == true)) {
                $_system_serial = ", geocontexter.gc_system_is_serial(id_keyword) AS system_serial";
            }

            $sql = 'SELECT  * '.$_system_serial.'
                    FROM  geocontexter.gc_keyword
                    WHERE id_keyword = ?';

            $result = $this->query($sql, array($params['id_keyword']), true);

            return $result->current();

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
        if (!isset($params['id_keyword'])) {
            throw new \Exception('id_keyword field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_keyword'])) {
            throw new \Exception('id_keyword isnt from type bigint');
        }


    }
}