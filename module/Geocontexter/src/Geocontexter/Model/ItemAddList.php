<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geoLister/
 * @package GeoContexter
 */

/**
 * Add item to one or more lists
 *
 * USAGE:
   <pre>
    $ItemAddList = $this->CoreModel('ItemAddList');

   $params  = array('id_item' => bigint,
                    'id_list' => bigint or array of bigints);

   $lastSequenceId = $ItemAddList->run( $params );

   // return last id or array of inserted ids

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 884 $ / $LastChangedDate: 2011-08-10 14:43:34 +0200 (Mi, 10 Aug 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ItemAddList extends AbstractModel
                  implements InterfaceModel
{

    /**
     * add Lists
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            if (is_array($params['id_list'])) {

                $__id_list = array();

                foreach ($params['id_list'] as $id_list) {

                    $this->insert('gc_list_item', 'geocontexter',
                                  array('id_item'         => $params['id_item'],
                                        'id_list'         => $id_list,
                                        'preferred_order' => new \Zend\Db\Sql\Expression("geocontexter.gc_get_new_entry_order(
                                                                                               'gc_list_item',
                                                                                               'id_item',
                                                                                               {$params['id_item']})")
                                           )
                                     );

                    $id_list     = $this->query("SELECT currval('geocontexter.seq_gc_list_item') AS id_list");
                    $__id_list[] = $id_list[0]['id_list'];
                }

            } else {

                $__id_list = false;

                $this->insert('gc_list_item', 'geocontexter',
                                     array('id_item'         => $params['id_item'],
                                           'id_list'         => $params['id_list'],
                                           'preferred_order' => new \Zend\Db\Sql\Expression("geocontexter.gc_get_new_entry_order(
                                                                                                'gc_list_item',
                                                                                                'id_item',
                                                                                                {$params['id_item']})")
                                          )
                                 );

                $id_list     = $this->query("SELECT currval('geocontexter.seq_gc_list_item') AS id_list");
                $__id_list = $id_list[0]['id_list'];

            }

            $this->commit();

            return $__id_list;

        } catch(\Exception $e) {
            $this->rollback();
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
        $val_digits = new \Zend\Validator\Digits();

        if (!isset($params['id_list'])) {
            throw new \Exception('id_list isnt defined');
        } elseif(is_array($params['id_list'])) {
            foreach ($params['id_list'] as $id_list) {
                if(false === $val_digits->isValid($id_list))
                {
                    throw new \Exception('id_list in array isnt from type bigint: '.var_export($id_list,true));
                }
            }
        } else {
            if (false === $val_digits->isValid($params['id_list'])) {
                throw new \Exception('id_list isnt from type bigint: '.var_export($params['id_list'],true));
            }
        }

        if (!isset($params['id_item'])) {
            throw new \Exception('id_item isnt defined');
        }

        if (false === $val_digits->isValid($params['id_item'])) {
            throw new \Exception('id_item isnt from type bigint');
        }
    }
}
