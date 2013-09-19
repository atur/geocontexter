<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * delete attribute group
 *
 * It only works if there is no content associated with an attribute group to delete
 *
 *  USAGE:
   <pre>
    $attribute_group_delete = $this->CoreModel('AttributeGroupDelete');

   // delete attribute group
   //
   $params = array('id_group' => bigint);

   $attribute_group_delete->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeGroupDelete extends    AbstractModel
                                      implements InterfaceModel
{
  private $table = array('2' => array('table' => 'gc_list',
                                      'id'    => 'id_list',
                                      'title' => 'title'),
                         '4' => array('table' => 'gc_keyword',
                                      'id'    => 'id_keyword',
                                      'title' => 'title'),
                         '3' => array('table' => 'gc_item',
                                      'id'    => 'id_item',
                                      'title' => 'title'),
                         '1' => array('table' => 'gc_record',
                                      'id'    => 'id_record'));

    /**
     * delete attributes group
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            // check if there is content associated with the attribute group
            //
            if (false !== ($content = $this->isContent( $params['id_group'] ))) {
                // if yes, we dont delete the attribute group
                //
                return $content;
            }

            // we delete the attribute
            //
            $this->delete('gc_attribute_group','geocontexter', array('id_group' => $params['id_group']));

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * check if content is related to the attribute group
     *
     *
     * @param array $params
     * @param bool $get_result
     * @return bool or result content if $get_result is true
     */
    public function isContent( $id_group, $get_result = false )
    {
        // we need the table which is associated with the id_attribute
        //
        $sql = '
              SELECT
                id_table
              FROM
                geocontexter.gc_attribute_group
              WHERE
                id_group = ?';

        $attribute_group_table = $this->query($sql, array($id_group));

        $id_table = $attribute_group_table[0]['id_table'];

        // link table from which we delete related attributes
        //
        $_join = 'geocontexter.'.$this->table[$id_table]['table'].' AS gct';

        // if we delete attributes from gc_record area
        // we link gc_items to the records
        //
        if ($id_table == 1) {
            $_join .= ' INNER JOIN geocontexter.gc_record AS gcr
                                ON gct.id_record = gcr.id_record';

            $_join .= ' INNER JOIN geocontexter.gc_item AS gci
                                ON gcr.id_item = gci.id_item';

                  // we fetch items which were recorded
                  //
            $id_name    = 'gci.id_item';
            $title_name = 'gci.title';

        } else {

            $id_name    = 'gct.' . $this->table[$id_table]['id'];
            $title_name = 'gct.' . $this->table[$id_table]['title'];
        }

        // now we check if there are table entries which use the attribute to delete
        //
        $sql = 'SELECT '.$id_name.', '.$title_name.'

            FROM    '.$_join.'

                WHERE   gct.id_attribute_group = ?';

        $rows = $this->query($sql, array($id_group));

        // We dont delete this attribute if there are entries
        //
        if (count($rows) > 0)  {
            // content which make use of the attribute group
            //
            if(false === $get_result)
            {
              return true;
            }
            return $rows;
        }

        return false;
    }

    /**
     * validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( $params)
    {
        if (!isset($params['id_group'])) {
            throw new \Exception(id_group . ' isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_group'])) {
            throw new \Exception('id_group isnt from type bigint');
        }
    }
}