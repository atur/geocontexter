<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get project subtree structure from a given id_project
 *
   USAGE:
   <pre>

   $ProjectGetTree = $this->CoreModel('ProjectGetTree');

   $params  = array('id_project'         => bigint id_project,
                    'exclude_id_project' => bigint id_project);

   $result  = $ProjectGetTree->run( $params );

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

class ProjectGetTree extends    AbstractModel
                     implements InterfaceModel
{
    private $result_tree = array();
    private $exclude_id  = false;

    /**
     * get contexte tree from id_project
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            if (isset($params['exclude_id_project'])) {
              $this->exclude_id = $params['exclude_id_project'];
            }

            $this->_sql_and = "";

            if (isset($params['has_controller'])) {
                $this->_sql_and = "AND controller IS NOT NULL\n";
            }

            if (isset($params['status'])) {
                $this->_sql_and .= "AND id_status {$params['status'][0]} {$params['status'][1]}\n";
            }

            $this->select_tree($params['id_project']);
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
        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }

        if (isset($params['exclude_id_project'])) {
            if (false === $val_digits->isValid($params['exclude_id_project'])) {
              throw new \Exception('exclude_id_project isnt from type bigint');
            }
        }

        if (isset($params['status'])) {
            if (!isset($params['status'][0])) {
                throw new \Exception('status array index 0 value not set');
            }
            if (!isset($params['status'][1])) {
                throw new \Exception('status array index 1 value not set');
            }

            if (!in_array($params['status'][0], array(">","<",">=","<=","=","!="))) {
                throw new \Exception('status array index 0 value is wrong: ' . var_export($params['status'][0],true));
            }

            if (!in_array($params['status'][1], array(0,100,200,300))) {
                throw new \Exception('status array index 1 value is wrong: ' . var_export($params['status'][1],true));
            }
        }
    }

    /**
     * recursive load project tree in an array
     *
     * @param int $id_parent
     * @param int $level indent level of nodes
     */
    private function select_tree( $id_parent=0, $level=0)
    {
        $sql = "
            SELECT
                id_project,id_parent,title,id_status,lang,controller
            FROM
                geocontexter.gc_project
            WHERE
                id_parent=?
                {$this->_sql_and}
            ORDER BY title ASC";

        $result = $this->query($sql, array($id_parent));

        foreach ($result as $row) {

          if (strcmp($row['id_project'], $this->exclude_id) == 0) {
            continue;
          }

            $row['level'] = $level;
            $this->result_tree[] = $row;
            $this->select_tree($row['id_project'], $level+1 );
        }

        return;
    }
}