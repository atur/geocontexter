<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Export one or more lists and its items
 *
 * It generates an xml file that contains:
   <ul>
   <li>Lists</li>
   <li>Lists attributes relations</li>
   <li>Lists keyword relations</li>
   <li>Items</li>
   <li>Items attributes relations</li>
   <li>Items keywords relations</li>
   <li>Attributes</li>
   <li>Keywords</li>
   </ul>
 *
 *
   USAGE:
   <pre>

   $ListExport = $this->CoreModel('ListExport');

   $params  = array('id_lists' => array); // id_list // required

   // return filename - in dir /public/data/export/
   //
   $export_file  = $ListExport->run( $params );

   if ($export_file instanceof \Core\Library\Exception) {
       return $this->error( $export_file->getMessage(), __file__, __line__);
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

class ListExport extends    AbstractModel
                 implements InterfaceModel
{
    var $lists                      = array();
    var $result_list_tree           = array();
    var $id_keyword                 = array();
    var $id_list_keyword            = array();
    var $keyword_branch             = array();
    var $id_list_item               = array();
    var $id_list_item_keyword       = array();
    var $id_attribute_group         = array();
    var $id_attribute               = array();

    var $__attributes = array();
    var $__keywords   = array();

    /**
     * export
     *
     * @param array $params
     * @return string Filename - model error instance on error
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $file_name   = 'geocontexter_list_item_backup_'.uniqid('', true).'.xml';
            $export_path = realpath(APPLICATION_PATH . '/../public/data/export/');

            // get system info for including in export file
            //
            $system = $this->CoreModel('SystemGet');

            $system_result  = $system->run( $params );

            if ($system_result instanceof \Core\Library\Exception) {
                throw new \Exception( $system_result->getMessage() );
            }

            // start export
            //
            $this->xml_export = $this->CoreModel('BackupXmlWriter');

            $this->xml_export->init($export_path . '/' .$file_name, $system_result);

            // ### start xml node for table gc_list ###
            //
            $this->xml_export->addTable('gc_list');
            $this->xml_export->addTableDefinition( $this->get_table_definition( 'gc_list' ) );
            $this->export_list_tree( $params['id_lists'] );
            $this->xml_export->endTable();

            // ### start xml node for table gc_item ###
            //
            $this->xml_export->addTable('gc_item');
            $this->xml_export->addTableDefinition( $this->get_table_definition( 'gc_item' ) );
            $this->export_items();
            $this->xml_export->endTable();

            // ### start xml node for table gc_list_item ###
            //
            $this->xml_export->addTable('gc_list_item');
            $this->xml_export->addTableDefinition( $this->get_table_definition( 'gc_list_item' ) );
            $this->xml_export->addRows( $this->id_list_item );
            $this->xml_export->endTable();

            // ### start xml node for table gc_keyword ###
            //
            $this->xml_export->addTable('gc_keyword');
            $this->xml_export->addTableDefinition( $this->get_table_definition( 'gc_keyword' ) );
            sort($this->id_keyword);
            $this->xml_export->addRows( $this->id_keyword );
            $this->xml_export->endTable();

            // ### start xml node for table gc_list_item_keyword ###
            //
            $this->xml_export->addTable('gc_list_item_keyword');
            $this->xml_export->addTableDefinition( $this->get_table_definition( 'gc_list_item_keyword' ) );
            $this->xml_export->addRows( $this->id_list_item_keyword );
            $this->xml_export->endTable();

            // ### start xml node for table gc_list_keyword ###
            //
            $this->xml_export->addTable('gc_list_keyword');
            $this->xml_export->addTableDefinition( $this->get_table_definition( 'gc_list_keyword' ) );
            $this->xml_export->addRows( $this->id_list_keyword );
            $this->xml_export->endTable();

            // ### start xml node for table gc_attribute_group ###
            //
            $this->xml_export->addTable('gc_attribute_group');
            $this->xml_export->addTableDefinition( $this->get_table_definition( 'gc_attribute_group' ) );
            $this->xml_export->addRows( $this->id_attribute_group);
            $this->xml_export->endTable();

            // ### start table node gc_attribute ###
            //
            $this->xml_export->addTable('gc_attribute');
            $this->xml_export->addTableDefinition( $this->get_table_definition( 'gc_attribute' ) );
            $this->xml_export->addRows( $this->id_attribute );
            $this->xml_export->endTable();

            $this->xml_export->end();

            // compress export file if available
            //
            if (true === $this->xml_export->compress()) {
                return $file_name . '.zip';
            }

            return $file_name;

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
        if (!isset($params['id_lists'])) {
            throw new \Exception('"id_lists" array param isnt defined');
        }
        if (!is_array($params['id_lists'])) {
            throw new \Exception('"id_lists" isnt from type array');
        }
        if (count($params['id_lists']) == 0) {
            throw new \Exception('nothing to export: empty id_lists array');
        }
    }

    /**
     * export lists
     *
     * @param array list of list ids
     */
    private function export_list_tree( $lists )
    {
        // export the lists
        //
        foreach ($lists as $id_list) {
            // first we fetch the top list
            //
            $sql = "
                SELECT
                    *
                FROM
                    geocontexter.gc_list
                WHERE
                    id_list=?";

            $result = $this->query($sql, array($id_list));

            foreach ($result as $key => $row) {
                $this->lists[] = $row['id_list'];

                $this->fetch_attributes( $row['id_attribute_group'] );

                $this->fetch_list_keys( $row['id_list'] );

                $this->xml_export->addRows( $result );
            }

            $this->result_list_tree = array();

            // we fetch the list tree under the top list
            //
            $this->select_list_tree($id_list);

            // we get the list attributes and keywords
            // and write list ids in temporary array to fetch the items later
            //
            foreach ($this->result_list_tree as $__list) {
                $this->lists[] = $__list['id_list'];

                $this->fetch_attributes( $__list['id_attribute_group'] );

                $this->fetch_list_keys( $__list['id_list'] );
            }

            if (count($this->result_list_tree) > 0) {
                $this->xml_export->addRows( $this->result_list_tree );
            }
        }
    }

    /**
     * write items as xml node
     *
     * @param int $id_parent
     */
    private function export_items()
    {
        $_tmp = array();

        foreach ($this->lists as $id_list) {
            $sql = "
                SELECT
                    i.*
                FROM
                    geocontexter.gc_item AS i
                    INNER JOIN geocontexter.gc_list_item AS l
                      ON i.id_item = l.id_item
                WHERE
                    l.id_list = ?";

            $result = $this->query($sql, array($id_list));

            foreach ($result as $key => $row) {
                $this->fetch_item_list_keyword( $row['id_item'], $id_list );

                $this->fetch_attributes( $row['id_attribute_group'] );

                if (!isset($this->item[$row['id_item']])) {
                    $this->item[$row['id_item']] = true;
                    $_tmp[] = $row;
                }
            }
        }

        $this->xml_export->addRows( $_tmp );

        return;
    }

    /**
     * recursive load list tree in an array
     *
     * @param int $id_parent
     */
    private function select_list_tree( $id_parent=0 )
    {
        $sql = "
            SELECT
                *
            FROM
                geocontexter.gc_list
            WHERE
                id_parent=?
            ORDER BY title ASC";

        $result = $this->query($sql, array($id_parent));

        foreach ($result as $key => $row) {
            $this->result_list_tree[] = $row;
            $this->select_list_tree($row['id_list']);
        }

        return;
    }

    /**
     * get table definitions
     *
     * @param string $table_name
     * @return array
     */
    private function get_table_definition( $table_name )
    {
        $sql = 'SELECT * FROM geocontexter.gc_table_description( ? )';

        return $this->query($sql, array($table_name));
    }



    /**
     * recursive load keyword branch in an array
     *
     * @param bigint $id_keyword
     */
    private function fetch_keywords( $id_keyword )
    {
        if (isset($this->__keywords[$id_keyword])) {
            return;
        }

        $this->__keywords[$id_keyword] = true;

        $sql = "
            SELECT
                *
            FROM
                geocontexter.gc_keyword
            WHERE
                id_keyword = ?";

        $result = $this->query($sql, array($id_keyword));

        foreach ($result as $key => $row) {
            $this->id_keyword[] = $row;

            $this->fetch_attributes( $row['id_attribute_group'] );

            if ($row['id_parent'] == 0) {
                continue;
            }

            $this->fetch_keywords( $row['id_parent'] );
        }

        return;
    }

    /**
     * load list kewords relations in an array
     *
     * @param int $id_parent
     * @param int $level indent level of nodes
     */
    private function fetch_list_keys( $id_list)
    {
        $sql = "
            SELECT
                *
            FROM
                geocontexter.gc_list_keyword
            WHERE
                id_list=?";

        $result = $this->query($sql, array($id_list));

        foreach ($result as $key => $row) {
            $this->id_list_keyword[] = $row;

            $this->fetch_keywords( $row['id_keyword'] );
        }

        return;
    }

    /**
     * load item_list keywords relations in an array
     *
     * @param bigint $id_item
     * @param bigint $id_list
     */
    private function fetch_item_list_keyword( $id_item, $id_list )
    {
        $sql = "
            SELECT
                *
            FROM
                geocontexter.gc_list_item
            WHERE
                    id_item = {$id_item}
                AND id_list = {$id_list}";

        $result = $this->query( $sql );

        foreach($result as $key => $row)
        {
            $this->id_list_item[] = $row;

            $sql = "
                SELECT
                    *
                FROM
                    geocontexter.gc_list_item_keyword
                WHERE
                    id_list_item = ?";

            $_result = $this->query($sql, array($row['id_list_item']));

            foreach ($_result as $_key => $_row) {
                $this->id_list_item_keyword[] = $_row;
                $this->fetch_keywords( $_row['id_keyword'] );
            }
        }
        return;
    }


    /**
     * assign array with attributes and it groups
     *
     * @param bigint $id_attribute_group
     */
    private function fetch_attributes( $id_attribute_group )
    {
        if ($id_attribute_group == 'NULL') {
            return;
        }

        // check if the attribute group was previously selected
        //
        if (isset($this->__attributes[$id_attribute_group])) {
            return;
        }

        $this->__attributes[$id_attribute_group] = true;

        $sql = 'SELECT *
                FROM  geocontexter.gc_attribute_group
                WHERE id_group = ?';

        $_result = $this->query($sql, array($id_attribute_group));

        foreach ($_result as $_key => $_row) {
            $this->id_attribute_group[] = $_row;
        }

        $sql = 'SELECT *
                FROM  geocontexter.gc_attribute
                WHERE id_group = ?';

        $_result = $this->query($sql, array($id_attribute_group));

        foreach ($_result as $_key => $_row) {
            $this->id_attribute[] = $_row;
        }
    }
}
