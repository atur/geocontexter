<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get context subtree structure from a given id_context
 *
   USAGE:
   <pre>
   $context_get_tree = $this->CoreModel('ContextGetTree');

   $params  = array('id_context'         => bigint id_context,
                    'exclude_id_context' => bigint id_context);

   $this->view->result = $context_get_tree->run( $params );

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

class ContextGetTree extends    AbstractModel
                     implements InterfaceModel
{
    private $result_tree = array();
    private $exclude_id  = false;

    /**
     * get contexte tree from id_context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        if (isset($params['exclude_id_context'])) {
            $this->exclude_id = $params['exclude_id_context'];
        }

        $this->select_tree($params['id_context']);
        return $this->result_tree;
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        $val_digits = new \Zend\Validator\Digits();

        if (!isset($params['id_context'])) {
            throw new \Exception('id_context field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_context'], 'Digits')) {
            throw new \Exception('id_context isnt from type bigint');
        }

        if (isset($params['exclude_id_context'])) {
            if (false === $val_digits->isValid($params['exclude_id_context'], 'Digits')) {
                throw new \Exception('exclude_id_context isnt from type bigint');
            }
        }
    }

    /**
     * recursive load context tree in an array
     *
     * @param int $id_parent
     * @param int $level indent level of nodes
     */
    private function select_tree( $id_parent=0, $level=0)
    {
        $sql = "
            SELECT
                id_context,id_parent,title,id_status,lang
            FROM
                geocontexter.gc_context
            WHERE
                id_parent=?
            ORDER BY title ASC";

        $result = $this->query($sql, array($id_parent));

        foreach ($result as $row) {
            if (strcmp($row['id_context'],$this->exclude_id) == 0) {
                continue;
            }

            $row['level'] = $level;
            $this->result_tree[] = $row;
            $this->select_tree($row['id_context'], $level+1 );
        }

        return;
    }
}