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

   $ItemUpdate = $this->CoreModel('ItemUpdate');

   $params  = array('id_item'          => bigint,
                    'remove_item_list' => array of bigints,
                    'data'             => array('title'              => string,
                                                'description'        => string,
                                                'lang'               => string,
                                                'attribute_value'    => string,          // json encoded
                                                'synonym_of'         => bigint (string), // bigint
                                                'id_attribute_group' => bigint (string), // bigint
                                                'files_folder'       => string,
                                                'id_status'          => smallint));

   $result  = $ItemUpdate->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
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

class ItemUpdate extends    AbstractModel
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
                                    'id_attribute_group' => true,
                                    'id_status'          => true,
                                    'synonym_of'         => true,
                                    'lang'               => true,
                                    'files_folder'       => true,
                                    'attribute_value'    => true
                                    );

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

            $this->update('gc_item', 'geocontexter', $params['data'], array('id_item' => $params['id_item']));

            if (isset($params['remove_item_list']) && is_array($params['remove_item_list'])) {
                $this->remove_item_from_lists( $params['id_item'], $params['remove_item_list'] );
            }

            if (isset($params['remove_id_keyword']) && (count($params['remove_id_keyword']) > 0)) {
                $this->remove_item_keywords( $params['id_item'], $params['remove_id_keyword'] );
            }

            if (isset($params['remove_list_id_keyword']) && (count($params['remove_list_id_keyword']) > 0)) {
                $this->remove_list_item_keywords( $params['id_list_item'], $params['remove_list_id_keyword'] );
            }

            $this->commit();

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
    private function remove_item_from_lists( $id_item, $lists )
    {
        foreach ($lists as $list) {
            $this->delete('gc_list_item', 'geocontexter', array('id_item' => $id_item, 'id_list' => $list));
        }

        // correct list order
        //
        $this->query('SELECT geocontexter.gc_item_correct_list_order(?)', array($id_item));
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
            if(empty($params['data']['title']))
            {
                throw new \Exception('List title is empty');
            }
        }

        if (!isset($params['id_item'])) {
            throw new \Exception('id_item field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_item'])) {
            throw new \Exception('id_item isnt from type bigint');
        }

        if (isset($params['preferred_list'])) {
            if (false === $val_digits->isValid($params['preferred_list'])) {
                throw new \Exception('preferred_list isnt from type bigint');
            }
        }


    }

    /**
     * remove item keywords
     *
     * @param string $id_item bigint
     * @param array $keywords
     */
    private function remove_item_keywords( $id_item, $keywords )
    {
        $val_digits = new \Zend\Validator\Digits();

        foreach ($keywords as $id_keyword) {

            if (false === $val_digits->isValid($id_keyword)) {
                throw new \Exception('id_keyword isnt from type bigint');
                continue;
            }

            $this->delete('gc_item_keyword', 'geocontexter', array('id_keyword' => $id_keyword, 'id_item' => $id_item));
        }
    }

    /**
     * remove list item keywords
     *
     * @param string $id_list_item bigint
     * @param array  $keywords
     */
    private function remove_list_item_keywords( $id_list_item, $keywords )
    {
        $val_digits = new \Zend\Validator\Digits();

        foreach ($keywords as $id_keyword) {
            if (false === $val_digits->isValid($id_keyword)) {
                throw new \Exception('id_keyword isnt from type bigint');
                continue;
            }

            $this->delete('gc_list_item_keyword', 'geocontexter', array('id_keyword' => $id_keyword, 'id_list_item' => $id_list_item));
        }
    }
}
