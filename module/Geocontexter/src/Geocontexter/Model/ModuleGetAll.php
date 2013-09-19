<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get users
 *
   USAGE:
   <pre>

   $ModuleGetAll = $this->CoreModel('ModuleGetAll');

   $params = array('status'  => int); // optional > module status

   $result  = $ModuleGetAll->run( $params );

   if ($result instanceof \Core\Library\Exception) {
        return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = $result;
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

class ModuleGetAll extends    AbstractModel
                   implements InterfaceModel
{
    private $status = "";

    /**
     * get users
     *
     *
     * @param array $params
     */
    public function run($params = array())
    {
        try {

            $this->validate_params($params);

            $sql = 'SELECT  *
                    FROM  geocontexter.gc_module
                    WHERE id != 1
                    '.$this->status.'
                    ORDER BY module_rank';

            return $this->query($sql);

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
        if (isset($params['status'])) {

            $val_int = new \Zend\Validator\Int();

            if (false === $val_int->isValid($params['status'])) {
                throw new \Exception('status isnt from type int');
            } else {
                $this->status = "AND module_status = {$params['status']}";
            }
        }
    }
}