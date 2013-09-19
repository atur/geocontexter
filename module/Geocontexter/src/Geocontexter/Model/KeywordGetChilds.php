<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get keyword childs from id_parent
 *
   USAGE:
   <pre>

   $KeywordGetChilds = $this->CoreModel('KeywordGetChilds');

   $params  = array('id_parent' => bigint,

                    // get keywords in preferred order
                    //
                    'preferred_order' => string, // asc or desc

                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   //
                   'system_serial' => bool); // optional , value: true

   $result  = $KeywordGetChilds->run( $params );

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

class KeywordGetChilds extends    AbstractModel
                       implements InterfaceModel
{
    /**
     * get keywordes from id_parent
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            // if the system_serial check must be included
            //
            $_system_serial = "";
            if (isset($params['system_serial']) && $params['system_serial'] == true) {
                $_system_serial = ", geocontexter.gc_system_is_serial(id_keyword) AS system_serial";
            }

            $_order = ' grk.title';
            if (isset($params['preferred_order'])) {
                $_order = ' grk.preferred_order ' . $params['preferred_order'];
            }

            $sql = 'SELECT  grk.* '.$_system_serial.',
                            (SELECT count(id_keyword)
                             FROM geocontexter.gc_keyword
                             WHERE id_parent = grk.id_keyword) AS num_childs
                    FROM  geocontexter.gc_keyword AS grk
                    WHERE grk.id_parent = ?
                    ORDER BY ' . $_order;

            return $this->query($sql, array($params['id_parent']));

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
        if (!isset($params['id_parent'])) {
            throw new \Exception('id_parent field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_parent'])) {
            throw new \Exception('id_parent isnt from type bigint');
        }

        if (isset($params['preferred_order'])) {
            if (!in_array($params['preferred_order'], array("asc","desc"))) {
                throw new \Exception('preferred_order must be from type string. Allowed strings "asc" or "desc"');
            }
        }

        
    }
}