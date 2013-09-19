<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Upgrade geocontexter module
 *
 * @package GeoContexter
 * @subpackage Module_System
 * @license http://www.opensource.org/licenses/bsd-license.php BSD
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class Upgrade extends    AbstractModel
              implements InterfaceModel
{
    public function run( $current_version, $new_version )
    {
        try
        {
            $this->beginTransaction();

            // do upgrade
            //
            if (0 == version_compare('1.0', $current_version) ) {
                // upgrade from module version 0.1 to 0.2
                //$this->upgrade_1_0_to_1_1();
                $current_version = '1.1';
            }

            // update to new module version number
            $this->setNewModuleVersionNumber( $new_version );

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * update to new module version number
     *
     * @param string $version  New module version number
     */
    private function setNewModuleVersionNumber( $version )
    {
        $sql = "UPDATE geocontexter.gc_module
                    SET module_version='{$version}'
                WHERE module_title = 'geocontexter'";

        $this->query($sql);
    }
}