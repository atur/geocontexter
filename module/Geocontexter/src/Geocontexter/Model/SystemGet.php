<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get system info
 *
   USAGE:
   <pre>
   $system_get = $this->CoreModel('SystemGet');

   $result  = $system_get->run();

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

class SystemGet extends AbstractModel
{
    /**
     * get system info
     *
     */
    public function run( $params = array() )
    {
        try {
            return $this->query('SELECT * FROM  geocontexter.gc_system LIMIT 1');
        } catch(\Exception $e) {
            throw $e;
        }
    }
}
