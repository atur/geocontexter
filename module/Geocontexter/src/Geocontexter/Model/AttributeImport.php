<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Import attribute groups and its attributes from xml backup file
 *
 *  USAGE:
   <pre>

    $AttributeImport = $this->CoreModel('AttributeImport');

    $AttributeImport->run(array('file' => string (full path to file)));

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Geocontexter\Model\BackupXmlReader;
use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeImport extends   BackupXmlReader
                                implements InterfaceModel
{
    /**
     * import
     * @param array $params
     */
    public function run( $params )
    {
        try {

            //$this->beginTransaction();

            //$this->validate_params($params);

            $this->openFile($params['file']);

            $this->create_rule_replace_insert_attribute_group();

            $this->create_rule_replace_insert_attribute();

            $this->xml_read();

            $this->delete_rule( 'replace_insert_attribute_group' , 'geocontexter.gc_attribute_group');

            $this->delete_rule( 'replace_insert_attribute'  , 'geocontexter.gc_attribute');

            $this->delete_rule( 'correct_attribute_group_sequence' , 'geocontexter.gc_attribute_group');

            $this->delete_rule( 'correct_attribute_sequence'  , 'geocontexter.gc_attribute');

            //$this->commit();

        } catch(\Exception $e) {
            //$this->rollback();
            throw $e;
        }
    }

    /**
     * this methode is called by the extended xmlreader class
     *
     * @param array $row Row to insert in db table
     * @return bool true on success else false
     */
    protected function insert( $row )
    {
        if ($this->_table_name == 'gc_attribute_group') {
            $attribute_group_add = $this->CoreModel('AttributeAddGroup');

            $row['id_table']  = (int)$row['id_table'];
            $row['id_status'] = (int)$row['id_status'];

            $result  = $attribute_group_add ->run( $row );
        } elseif($this->_table_name == 'gc_attribute') {
            $attribute_add = $this->CoreModel('AttributeAdd');

            $result  = $attribute_add->run( $row );
        }
    }

    /**
     * create rule for insert attribute groups
     * if group exists only update some fields of the existing attribut group
     *
     */
    private function create_rule_replace_insert_attribute_group()
    {
        $sql = 'CREATE OR REPLACE RULE replace_insert_attribute_group AS
                   ON INSERT TO geocontexter.gc_attribute_group
                   WHERE
                     EXISTS(SELECT 1 FROM geocontexter.gc_attribute_group WHERE id_group = NEW.id_group)
                   DO INSTEAD
                   (UPDATE geocontexter.gc_attribute_group
                           SET lang        = NEW.lang,
                               description = NEW.description,
                               title       = NEW.title,
                               update_time = NEW.update_time
                       WHERE id_group    = NEW.id_group
                       AND   update_time < NEW.update_time)';

        // When we import attribute groups we need to import the id_group field of each row.
        // If we import in an installation which has the same namespace and application id
        // the import run into conflict when the imported id_group is higher than
        // the existing sequence. Therefore we need to correct the sequence number
        //
        $nextval = 'SELECT nextval(\'geocontexter.seq_gc_attribute_group\')';

        $sql2 = 'CREATE OR REPLACE RULE correct_attribute_group_sequence AS
                   ON INSERT TO geocontexter.gc_attribute_group
                   WHERE
                     geocontexter.gc_system_is_serial(NEW.id_group) IS NOT NULL
                     AND
                     currval(\'geocontexter.seq_gc_attribute_group\') <= NEW.id_group
                   DO ALSO
                   (SELECT setval(\'geocontexter.seq_gc_attribute_group\', NEW.id_group))';

        $this->query($nextval);
        $this->query($sql);
        $this->query($sql2);
    }

    /**
     * create rule for insert attributes
     * if attribute exists only update some fields of the existing attribut
     *
     */
    private function create_rule_replace_insert_attribute()
    {
        $sql = 'CREATE OR REPLACE RULE replace_insert_attribute AS
                   ON INSERT TO geocontexter.gc_attribute
                   WHERE
                     EXISTS(SELECT 1 FROM geocontexter.gc_attribute WHERE id_attribute = NEW.id_attribute)
                   DO INSTEAD
                   (UPDATE geocontexter.gc_attribute
                           SET lang                   = NEW.lang,
                               default_display        = NEW.default_display,
                               update_time            = NEW.update_time,
                               attribute_name         = NEW.attribute_name,
                               attribute_title        = NEW.attribute_title,
                               attribute_description  = NEW.attribute_description,
                               attribute_required     = NEW.attribute_required,
                               attribute_unit         = NEW.attribute_unit,
                               attribute_regex        = NEW.attribute_regex
                       WHERE id_attribute = NEW.id_attribute
                       AND   update_time  < NEW.update_time)';

        // When we import attributes we need to import the id_attribute field of each row.
        // If we import in an installation which has the same namespace and application id
        // the import run into conflict when the imported id_attribute is higher than
        // the existing sequence. Therefore we need to correct the sequence number
        //
        $nextval = 'SELECT nextval(\'geocontexter.seq_gc_attribute\')';

        $sql2 = 'CREATE OR REPLACE RULE correct_attribute_sequence AS
                   ON INSERT TO geocontexter.gc_attribute
                   WHERE
                     geocontexter.gc_system_is_serial(NEW.id_attribute) IS NOT NULL
                     AND
                     currval(\'geocontexter.seq_gc_attribute\') <= NEW.id_attribute
                   DO ALSO
                   (SELECT setval(\'geocontexter.seq_gc_attribute\', NEW.id_attribute))';

        $this->query($nextval);
        $this->query($sql);
        $this->query($sql2);
    }

    /**
     * delete rules
     *
     * @param string $rule Rule name
     * @param string $table Table name
     */
    private function delete_rule( $rule, $table)
    {
        $sql = 'DROP RULE ' . $rule . ' ON ' . $table;
        $this->query($sql);
    }
}
