<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * compare current version against system version
 * see php function version_compare
   USAGE:
   <pre>

   $UpgradeCheck = $this->CoreModel('UpgradeCheck');

   $result  = $UpgradeCheck->run( array('geocontexter_version' => string) );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
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

class UpgradeCheck extends    AbstractModel
                   implements InterfaceModel
{
    /**
     * get contextes from id_context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $sql = 'SELECT system_version FROM  geocontexter.gc_system';

            $result = $this->query($sql);

            return version_compare($result[0]['system_version'], $params['geocontexter_version']);

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
        if (!isset($params['geocontexter_version'])) {
            throw new \Exception('geocontexter_version field isnt defined');
        }
    }
}