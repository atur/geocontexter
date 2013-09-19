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
   $user = new Geocontexter_Model_UserGetUsers;
   $UserGetUsers = $this->CoreModel('UserGetUsers');

   $params = array('id_group'  => int,     // optional
                   'id_status' => int,     // optional
                   'order'     => string); // optional

   $result  = $UserGetUsers->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result         = $result;
       $this->view->total_num_rows = $user->totalNumRows();
   }

    // id_group
    //
    //0   = Superuser     - All rights. Only one user can have superuser rights
    //100 = Administrator - All rights. Except add or delete administrator
    //200 = Editor        - Can add, modify, delete all records
    //300 = Contributor   - Can add, modify, delete own record
    //400 = Webservice    - Access to webservices
    //500 = People        - No rights

    // id_status
    //
    //0   = trash
    //100 = inactive
    //200 = active
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

class UserGetUsers extends    AbstractModel
                   implements InterfaceModel
{
    /**
     * get users
     *
     *
     * @param array $params
     */
    public function run($params)
    {
        try {

            $this->validate_params($params);

            // if the system_serial check must be included
            //
            $_system_serial = "";
            if (isset($params['system_serial']) && ($params['system_serial'] == true)) {
                $_system_serial = ", geocontexter.gc_system_is_serial(id_user) AS system_serial";
            }

            $_sql_limit = '';
            if (isset($params['limit']))  {
                $_sql_limit = 'LIMIT ' . $params['limit'][0] . ' OFFSET ' . $params['limit'][1];
            }

            $sql = 'SELECT  * '.$_system_serial.'
                    FROM  geocontexter.gc_user
                    ORDER BY user_lastname, user_forename
                    ' . $_sql_limit;

            return $this->query($sql);

        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * total num rows without limit
     *
     * @return int
     */
    public function totalNumRows()
    {
        try
        {
            $sql = 'SELECT  count(id_user)
                    FROM  geocontexter.gc_user';

            return (int)$this->db->fetchOne($sql);
        }
        catch(Zend_Db_Exception $e)
        {
            throw new \Exception('Caught exception: ' . get_class($e));
            throw new \Exception('Message: ' . $e->getMessage());

            return new Mozend_ModelError( $this->get_error() );
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

    }
}