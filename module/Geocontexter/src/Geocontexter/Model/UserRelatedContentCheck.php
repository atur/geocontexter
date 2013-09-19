<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Check if user related content (records) exists
 *
   USAGE:
   <pre>
   $user = new Geocontexter_Model_UserRelatedContentCheck;
   $UserRelatedContentCheck = $this->CoreModel('UserRelatedContentCheck');

   $result  = $UserRelatedContentCheck->run( array('id_user' => bigint );

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

class UserRelatedContentCheck extends    AbstractModel
                              implements InterfaceModel
{

    /**
     * update context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $sql = 'SELECT id_record FROM geocontexter.gc_record_value WHERE id_owner = ?';

            return $this->query($sql, array($params['id_user']));

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
        if (!isset($params['id_user'])) {
            throw new \Exception('id_user field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_user'])) {
            throw new \Exception('id_user isnt from type bigint');
        }
    }
}