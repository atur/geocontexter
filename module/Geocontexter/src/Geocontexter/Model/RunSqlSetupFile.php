<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Run sql queries from module related setup files
 *
 * @package GeoContexter
 * @subpackage Module_System
 * @license http://www.opensource.org/licenses/bsd-license.php BSD
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class RunSqlSetupFile extends    AbstractModel
{
    public function __construct( $data )
    {
        $_sql_file = APPLICATION_PATH . '/modules/'.$data['module'].'/sql/'.$data['sql_file_name'];

        if (false !== ($_sql_content = file_get_contents($_sql_file))) {
            $search  = array("*namespace*",
                             "*serial*",
                             "*srid*",
                             "*serial_minvalue*",
                             "*serial_maxvalue*",
                             "*serial_start*",
                             "*version*",
                             "*superuser_login*",
                             "*superuser_password*",
                             "*superuser_timezone*");

            $this->set_serials( $data );

            $replace = array($data['namespace'],
                             $data['serial'],
                             $data['srid'],
                             $this->serial_minvalue,
                             $this->serial_maxvalue,
                             $this->serial_start,
                             $data['version'],
                             $data['superuser_login'],
                             md5($data['superuser_password']),
                             $data['superuser_timezone']);

            $_sql_content = str_replace($search,$replace,$_sql_content);

            $_sql = explode("--cut--",$_sql_content);

            foreach ($_sql as $_query) {
                if (false === $data['db']->exec($_query)) {
                    $_message =  array('error_source' => $_sql_file,
                                       'pg_info'      => $data['db']->errorInfo(),
                                       'query'        => $_query);

                    throw new \Exception('Setup sql error: ' .
                                         var_export($_message,true));
                }
            }
        } else {
            throw new \Exception('File dosent exists: ' . $_sql_file);
        }
    }

    private function set_serials( & $data )
    {
        if ($data['namespace'] == 'test') {
            $this->serial_minvalue = '-1999999999999999';
            $this->serial_maxvalue = '1999999999999999';
            $this->serial_start    = '1000000000000000';
        } else if($data['namespace'] == 'standalone') {
            $this->serial_minvalue = '-9223372036854775808';
            $this->serial_maxvalue = '9223372036854775807';
            $this->serial_start    = '1';
        } else {
            $serial_minvalue = $data['db']->query("SELECT (({$data['serial']} * 1000000000000000 + 999999999999999) * -1)");
            $serial_maxvalue = $data['db']->query("SELECT ({$data['serial']} * 1000000000000000 + 999999999999999)");
            $serial_start    = $data['db']->query("SELECT ({$data['serial']} * 1000000000000000)");
            $this->serial_minvalue = $serial_minvalue->fetchColumn();
            $this->serial_maxvalue = $serial_maxvalue->fetchColumn();
            $this->serial_start    = $serial_start->fetchColumn();
        }
    }
}