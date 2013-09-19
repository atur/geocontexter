<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get items from a list
 *
   USAGE:
   <pre>

   $ItemsGetFromList = $this->CoreModel('ItemsGetFromList');

   $params  = array('id_list' => bigint id_list,
                    'limit'   => int,     // optional
                    'offset'  => int,     // optional
                    'status'  => smallint // optional
                    );

   $result  = $ItemsGetFromList->run( $params );

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

class ItemsGetFromList extends    AbstractModel
                       implements InterfaceModel
{
    /**
     * get items from a list
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $where_status = '';

            if (isset($params['id_status'])) {
                $where_status = "AND gri.id_status = " . $params['id_status'];
            }

            $sql_limit = '';

            if (isset($params['limit'])) {
                $sql_limit = " LIMIT " . $params['limit'];
            }

            $sql_offset = '';

            if (isset($params['offset'])) {
                $sql_offset = " OFFSET " . $params['offset'];
            }

            $sql = 'SELECT  gri.*
                    FROM  gc_item AS gri
                    WHERE gri.id_list = ?
                    '.$where_status.'
                    ORDER BY gri.title
                    '.$sql_limit.'
                    '.$sql_offset;

            return $this->query($sql, array($params['id_list']));

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
        if (!isset($params['id_list'])) {
            throw new \Exception('id_list field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_list'])) {
            throw new \Exception('id_list isnt from type bigint');
        }

        $val_int = new \Zend\Validator\Int();

        if (isset($params['id_status'])) {
            if (false === $val_int->isValid($params['id_status'])) {
                throw new \Exception('id_status isnt from type smallint');
            }
        }

        if (isset($params['limit'])) {
            if (false === $val_int->isValid($params['limit'])) {
                throw new \Exception('limit isnt from type integer');
            }
        }

        if (isset($params['offset'])) {
            if (false === $val_int->isValid($params['offset'])) {
                throw new \Exception('offset isnt from type integer');
            }
        }

        
    }
}
