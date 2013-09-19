<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get list subtree structure from a given id_list
 *
   USAGE:
   <pre>
   $list_get_tree = $this->CoreModel('ListGetTree');

   $params  = array('id_list'         => bigint id_list,
                    'exclude_id_list' => bigint id_list);

   $result  = $list_get_tree->run( $params );

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

class ListGetTree extends    AbstractModel
                   implements InterfaceModel
{
    private $result_tree = array();
    private $exclude_id  = false;

    /**
     * get contexte tree from id_list
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            if (isset($params['exclude_id_list'])) {
                $this->exclude_id = $params['exclude_id_list'];
            }

            $this->select_tree($params['id_list']);

            return $this->result_tree;

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

        if (isset($params['exclude_id_list'])) {
            if (false === $val_digits->isValid($params['exclude_id_list'])) {
                throw new \Exception('exclude_id_list isnt from type bigint');
            }
        }


    }

    /**
     * recursive load list tree in an array
     *
     * @param int $id_parent
     * @param int $level indent level of nodes
     */
    private function select_tree( $id_parent=0, $level=0)
    {
        $sql = "
            SELECT
                id_list,id_parent,title,id_status,lang
            FROM
                geocontexter.gc_list
            WHERE
                id_parent=?
            ORDER BY title ASC";

        $result = $this->query($sql, array($id_parent));

        foreach ($result as $key => $row) {
            if (strcmp($row['id_list'],$this->exclude_id) == 0) {
                continue;
            }

            $row['level']        = $level;
            $this->result_tree[] = $row;
            $this->select_tree($row['id_list'], $level+1 );
        }

        return;
    }
}