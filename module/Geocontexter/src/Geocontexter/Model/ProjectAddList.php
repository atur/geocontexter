<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add project lists
 *
   USAGE:
   <pre>

   $ProjectAddList = $this->CoreModel('ProjectAddList');

   $params  = array('id_project' => bigint,
                    'id_list'    => bigint or array of bigints);

   $result = $ProjectAddList->run( $params );

    if ($result instanceof \Core\Library\Exception) {
        return $this->error( $result->getMessage(), __file__, __line__);
    }

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

class ProjectAddList extends    AbstractModel
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

            if (is_array($params['id_list'])) {

                foreach ($params['id_list'] as $id_list) {

                    if (false === $this->list_exists($id_list, $params['id_item'])) {
                        $_commit = true;
                        $this->insert('gc_project_list', 'geocontexter', array('id_project'  => $params['id_project'],
                                                                               'id_list'     => $id_list));
                    }
                }
            } else {

                if (false === $this->list_exists($params['id_list'], $params['id_project'])) {
                    $_commit = true;
                    $this->insert('gc_project_list', 'geocontexter', array('id_project' => $params['id_project'],
                                                                           'id_list'    => $params['id_list']));
                }
            }

            if (true === $_commit) {
                $this->commit();
                return true;
            }

            return false;

        } catch(\Exception $e) {
            if (true === $_commit) {
                $this->rollback();
            }
            throw $e;
        }
    }

    /**
     * check if id_project has linked id_list
     *
     * @param string $id_list
     * @param string $id_project
     * @return bool
     */
    private function list_exists( $id_list, $id_project )
    {
        $sql = "SELECT id_project FROM geocontexter.gc_project_list WHERE id_list = ? AND id_project = ?";

        $_id_project = $this->query($sql, array($id_list, $id_project));

        if (isset($_id_project[0]['id_project'])) {
            return true;
        }

        return false;
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

        if (!isset($params['id_list'])) {
            throw new \Exception('id_list isnt defined');
        } elseif(is_array($params['id_list'])) {
            foreach ($params['id_list'] as $id_list) {
                if (false === $val_digits->isValid($id_list)) {
                    throw new \Exception('id_list in array isnt from type bigint: '.var_export($id_list,true));
                }
            }
        } else {
            if (false === $val_digits->isValid($params['id_list'])) {
                throw new \Exception('id_list isnt from type bigint: '.var_export($params['id_list'],true));
            }
        }

        if (!isset($params['id_project'])) {
            throw new \Exception('id_project isnt defined');
        }

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}
