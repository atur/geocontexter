<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * check if records has relations to other tables
 *
   USAGE:
   <pre>
   $RecordHasRelation = $this->CoreModel('RecordHasRelation');

   $params  = array('id_name' => string   // id_list,id_item,id_context,id_project
                    'id'      => bigint); // id value

   $result  = $RecordHasRelation->run( $params );

    if ($result instanceof \Core\Library\Exception) {
        return $this->error( $result->getMessage(), __file__, __line__);
    } elseif(false === $result) {
        // no records
    } elseif(true === $result) {
        // one or more records
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

class RecordHasRelation extends    AbstractModel
                        implements InterfaceModel
{
    private $table = array('id_list'    => 'gc_list',
                           'id_item'    => 'gc_item',
                           'id_project' => 'gc_project',
                           'id_context' => 'gc_context');
    /**
     * @param array $params
     * @return bool or error object
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $sql = 'SELECT  r.id_record
                    FROM  geocontexter.'.$this->table[$params['id_name']].' AS p
                    INNER JOIN geocontexter.gc_record AS r
                        ON r.'.$params['id_name'].' = p.'.$params['id_name'].'
                    WHERE p.'.$params['id_name'].' = ?
                    LIMIT 1';

            $result = $this->query($sql, array($params['id']));

            if (isset($result[0]['id_record'])) {
                return true;
            } else {
                return false;
            }

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
        if (!isset($params['id_name'])) {
            throw new \Exception('id_name isnt defined');
        }

        if (!isset($params['id'])) {
            throw new \Exception('id_name isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id'])) {
            throw new \Exception('id isnt from type bigint');
        }

        if (!isset($this->table[$params['id_name']])) {
            throw new \Exception('id_name isnt recognized: ' . $params['id_name']);
        }


    }
}
