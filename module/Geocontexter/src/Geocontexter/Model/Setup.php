<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Setup model of the system module
 *
 * @package GeoContexter
 * @subpackage Module_System
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

class Setup
{

    public function __construct( $data )
    {
        $data['module']        = 'geocontexter';
        $data['sql_file_name'] = 'setup.sql';

        Mozend_ModelLoader::run( 'Geocontexter_Model_RunSqlSetupFile', $data );
    }
}