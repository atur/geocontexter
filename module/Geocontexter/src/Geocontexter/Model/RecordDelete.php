<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * delete record
 *
   USAGE:
   <pre>
   $record = new Geocontexter_Model_RecordDelete;
   $RecordDelete = $this->CoreModel('RecordDelete');

   $params  = array('id_record' => bigint,
                    'id_owner'  => bigint);

   $result  = $RecordDelete->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   // The $result value is the number of rows affected by the delete operation

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
  \*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class RecordDelete extends    AbstractModel
                   implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $_sql = '';

            $where = array('id_record' => $params['id_record']);

            if (isset($params['id_owner'])) {
                $where['id_owner'] = $params['id_owner'];
            }

            $result = $this->delete('gc_record', 'geocontexter', $where);

            $this->commit();

            return $result;

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * set and validate parameters
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_record'])) {
            throw new \Exception('id_record field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_record'])) {
            throw new \Exception('id_record isnt from type bigint');
        }

        if (isset($params['id_owner'])) {
            if (false === $val_digits->isValid($params['id_owner'])) {
                throw new \Exception('id_owner isnt from type bigint');
            }
        }
    }
}
