<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Update list
 *
   USAGE:
   <pre>

   $ListUpdate = $this->CoreModel('ListUpdate');

   $params  = array('id_list' => bigint id_list,
                    'data'    => array('title'              => string,
                                       'description'        => string,
                                       'id_parent'          => bigint,
                                       'id_attribute_group' => bigint (string),
                                       'id_status'          => smallint,
                                       'preferred'          => boolean));

   $result  = $ListUpdate->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 835 $ / $LastChangedDate: 2011-03-05 12:55:03 +0100 (Sa, 05 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ListUpdate extends    AbstractModel
                 implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('title'              => true,
                                    'description'        => true,
                                    'id_parent'          => true,
                                    'id_attribute_group' => true,
                                    'id_status'          => true,
                                    'preferred'          => true,
                                    'lang'               => true,
                                    'attribute_value'    => true);

    /**
     * update list
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $params['data']['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");

            $this->update('gc_list', 'geocontexter', $params['data'], array('id_list = ' . $params['id_list']));

            if (isset($params['remove_id_keyword']) && (count($params['remove_id_keyword']) > 0)) {
                $this->remove_list_keywords( $params['id_list'], $params['remove_id_keyword'] );
            }

            // we need the trash instance to move ids to and from trash
            //
            $trash = $this->CoreModel('Trash');

            if ($params['data']['id_status'] == 0) {
                $trash->toTrash( $params['id_list'], 2 );
            } else {
                // remove id from trash if present
                $trash->undoTrash( $params['id_list'], 2 );
            }

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * update list id_parent
     *
     * this methode is only called from the geocontexter ListSelectController
     *
     *
     * @param array $params
     */
    public function updateList( $params )
    {
        $data = array();

        if (!isset($params['id_list'])) {
            throw new \Exception('id_list field isnt defined');
        }

        if (false === Zend_Validate::is($params['id_list'], 'Digits')) {
            throw new \Exception('id_list isnt from type bigint');
        }

        if (!isset($params['id_parent'])) {
            throw new \Exception('id_parent field isnt defined');
        }

        if (false === Zend_Validate::is($params['id_parent'], 'Digits')) {
            throw new \Exception('id_parent isnt from type bigint');
        }

        $this->beginTransaction();

        try {

            $data['id_parent']   = new \Zend\Db\Sql\Expression($params['id_parent']);

            $data['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");

            $this->update('gc_list', 'geocontexter', $data, array('id_list = ' . $params['id_list']));

            $this->rebuild_gc_list_index();

            $this->commit();

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
        if (!isset($params['data'])) {
            throw new \Exception('data array isnt defined');
        }

        foreach ($params['data'] as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (isset($params['data']['title'])) {
            if (empty($params['data']['title'])) {
                throw new \Exception('List title is empty');
            }
        }

        if (!isset($params['id_list'])) {
            throw new \Exception('id_list field isnt defined');
        }

        if (isset($params['data']['preferred'])) {
            if (!is_bool($params['data']['preferred'])) {
                throw new \Exception('"preferred" isnt from type boolean');
            } else {
                if ($params['data']['preferred'] === true) {
                    $params['data']['preferred'] = "t";
                } else {
                    $params['data']['preferred'] = "f";
                }
            }
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_list'])) {
            throw new \Exception('id_list isnt from type bigint');
        }
    }

    /**
     * remove list keywords
     *
     *
     * @param string $id_list bigint
     * @param array $keywords
     */
    private function remove_list_keywords( $id_list, $keywords )
    {
        $val_digits = new \Zend\Validator\Digits();

        foreach ($keywords as $id_keyword) {
            if(false === $val_digits->isValid($id_keyword)) {
                throw new \Exception('id_keyword isnt from type bigint in function "remove_list_keywords": FILE: ' . __file__);
            }

            $this->delete('gc_list_keyword', 'geocontexter', array('id_keyword' => $id_keyword, 'id_list' => $id_list));
        }
    }

    /**
     * rebuild list index table
     *
     */
    private function rebuild_gc_list_index()
    {
        $this->query('TRUNCATE geocontexter.gc_list_index');
        $this->query('SELECT geocontexter.gc_list_index_add(id_list) FROM  geocontexter.gc_list');
    }
}
