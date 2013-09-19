<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Export one or more attribute groups and its attributes
 *
 *  USAGE:
 *  <pre>
 *  $attribute_groups = new Geocontexter_Model_AttributeExport;
 *
 *  $params  = array('id_groups' => array); // id_group ids // required
 *
 *  // return filename - in dir /public/data/export/
 *  //
 *  $export_file  = $attribute_groups->export( $params );
 *
 *  if($export_file instanceof Mozend_ModelError)
 *  {
 *      $result->logError( __file__, __line__ );
 *  }
 *  </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeExport extends    AbstractModel
                      implements InterfaceModel
{
    /**
     * export
     *
     * @param array $params
     * @return string Filename - model error instance on error
     */
    public function run( $params )
    {
        $file_name   = 'geocontexter_attribute_backup_'.uniqid('', true).'.xml';
        $export_path = realpath(GEOCONTEXTER_ROOT . '/public/data/export/');

        $this->validate_params($params);

        // get system info for including in export file
        //
        $system = $this->CoreModel('SystemGet');

        $system_result  = $system->run( $params );

        // start export
        //
        $XmlWriter = $this->CoreModel('BackupXmlWriter');

        $XmlWriter->init( $export_path . '/' .$file_name, $system_result );

        // --- start table node gc_attribute_group ---
        //
        $XmlWriter->addTable('gc_attribute_group');

        // add table definition node
        $XmlWriter->addTableDefinition( $this->get_table_definition( 'gc_attribute_group' ) );

        // export the attribute groups
        //
        foreach ($params['id_groups'] as $id_group) {

            $attribute_group = $this->CoreModel('AttributeGroupGet');

            $__params = array('id_group'    => $id_group,
                              'fetch_assoc' => true);

            $result  = $attribute_group->run( $__params);

            $XmlWriter->addRows( array($result) );
        }

        $XmlWriter->endTable();


        // --- start table node gc_attribute ---
        //
        $XmlWriter->addTable('gc_attribute');

        $XmlWriter->addTableDefinition( $this->get_table_definition( 'gc_attribute' ) );

        // export the groups attributes
        //
        foreach ($params['id_groups'] as $id_group) {

            $item_attributes = $this->CoreModel('AttributeGetGroupAttributes');

            $__params  = array('id_group' => $id_group);

            $result  = $item_attributes->run( $__params );

            $XmlWriter->addRows( $result );
        }

        $XmlWriter->endTable();

        $XmlWriter->end();

        if (true === $XmlWriter->compress()) {
          // if compression successfull return zip file
          return $file_name . '.zip';
        }

        return $file_name;
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_groups'])) {
            throw new \Exception('"id_groups" array param isnt defined');
        }
        if (!is_array($params['id_groups'])) {
            throw new \Exception('"id_groups" isnt from type array');
        }
        if (count($params['id_groups']) == 0) {
            throw new \Exception('nothing to export: empty id_groups array');
        }
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
}
