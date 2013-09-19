<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * delete single attribute or a whole attribute group
 *
 * It only works if there is no content associated with an attribute group to delete
 *
 *  USAGE:
   <pre>
    $attribute = $this->CoreModel('AttributeDelete');

   // to delete a single attribute
   //
   $params = array('id_attribute' => bigint);
   $attribute->deleteAttribute( $params );

   // OR to delete a whole attribute group
   //
   $params = array('id_attribute_group' => bigint);
   $result = $attribute->deleteAttributeGroup( $params );

    if (true === $result) {
      // successfull delete attribute
    } else {
      // delete of attribute or group failed because there are some table enries
      // which make use of this attribute group
      // $result contains the related entries
    }
   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 878 $ / $LastChangedDate: 2011-07-30 09:30:41 +0200 (Sa, 30 Jul 2011) $ / $LastChangedBy: armand.turpel@gmail.com $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class AttributeDelete extends AbstractModel
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
     * delete attributes
     *
     * @param array $params
     */
    public function deleteAttribute( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params, 'id_attribute');

            // we need the id_group of the attribute
            //
            $sql = '
              SELECT
                id_group, attribute_type
              FROM
                geocontexter.gc_attribute
              WHERE
                id_attribute = ?';

            $attr_result = $this->query($sql, array($params['id_attribute']));

            // check if there is content associated with the attribute group
            //
            if (false !== ($content = $this->isContent( $attr_result['id_group'] ))) {
                // if yes, we dont delete the attribute
                //
                return $content;
            }

            // we delete the attribute
            //
            $this->delete('gc_attribute','geocontexter', array('id_attribute' => $params['id_attribute']));

            // correct order and index of other attributes in this group
            //
            $this->query('SELECT geocontexter.gc_attribute_correct_order_index(?)', array($attr_result['id_group']));

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * delete attribute group
     *
     * @param array $params
     */
    public function deleteAttributeGroup( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params( $params, 'id_attribute_group' );

            if ($content = $this->isContent( $params['id_attribute_group'] )) {
              return $content;
            }

            // delete all attributes of a group
            //
            $this->delete('gc_attribute','geocontexter', array(id_group => $params['id_attribute_group']));

            // delete the attribute group
            //
            $this->delete('gc_attribute_group','geocontexter', array(id_group => $params['id_attribute_group']));

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * check if content is related to the attribute group
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
        $_join = 'geocontexter.'.$this->table[$id_table]['table'].' AS gct ';

        // if we delete attributes from gc_record area
        // we link gc_items to the records
        //
        if ($id_table == 1) {
          $_join .= 'INNER JOIN geocontexter.gc_record AS gcr
                              ON gct.id_record = gcr.id_record ';

          $_join .= 'INNER JOIN geocontexter.gc_item AS gci
                              ON gcr.id_item = gci.id_item ';

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

        // We dont delete this attribute if there entries
        //
        if (count($rows) > 0) {
          // content which make use of the attribute group
          //
          if (false === $get_result) {
            return true;
          }
          return $rows;
        }

        return false;
    }

    /**
     * validate parameters
     *
     * @param array $params
     */
    private function validate_params( & $params, $name )
    {
        if (!isset($params[$name])) {
            throw new \Exception($name . ' field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params[$name])) {
            throw new \Exception($name . ' isnt from type bigint');
        }


    }
}
