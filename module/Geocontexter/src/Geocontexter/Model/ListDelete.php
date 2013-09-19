<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * delete list and all what depends on it
 *
 * USAGE:
  <pre>
   $list_delete = $this->CoreModel('ListDelete');

  $params  = array('id_list' => bigint); // optional , value: true

  $result  = $list_delete->run( $params );

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
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (dim., 27 févr. 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ListDelete extends    AbstractModel
                 implements InterfaceModel
{
    /**
     * get list from id_list
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $list_tree = $this->CoreModel('ListGetTree');

            $params  = array('id_list'  => $params['id_list'] );

            $result  = $list_tree->run( $params );

            if ($result instanceof \Core\Library\Exception) {
                return $result;
            }

            foreach ($result as $list) {
                $result = $this->delete("gc_list", "geocontexter", array("id_list" => $list['id_list']));

                if ($result instanceof \Core\Library\Exception) {
                    return $this->error( $result->getMessage(), __file__, __line__);
                }
            }

            $result = $this->delete("gc_list", "geocontexter", array("id_list" => $params['id_list']));

            if ($result instanceof \Core\Library\Exception) {
                return $this->error( $result->getMessage(), __file__, __line__);
            }

            // fetch all list items which have no list and delete them
            //
            $items = $this->CoreModel('ItemGetListRelated');

            $params  = array('id_list'                  => 0,
                             'no_transform_attributes'  => true);

            $result  = $items->run( $params );

            if ($result instanceof \Core\Library\Exception) {
                return $this->error( $result->getMessage(), __file__, __line__);
            }

            if (is_array($result)) {
                foreach ($result as $item => $val) {
                    $result = $this->delete("gc_item", "geocontexter", array("id_item" => $val['id_item']));

                    if ($result instanceof \Core\Library\Exception) {
                        return $this->error( $result->getMessage(), __file__, __line__);
                    }
                }
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
        if (!isset($params['id_list'])) {
            throw new \Exception('id_list field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_date->isValid($params['id_list'])) {
            throw new \Exception('id_list isnt from type bigint');
        }
    }
}
