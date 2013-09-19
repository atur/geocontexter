<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Import lists
 *
   USAGE:
   <pre>
   // $file = uploaded file
   //
   $lists = new Geocontexter_Model_ListImport( $file );

   $result  = $lists->import( $params );

   if($export_file instanceof Mozend_ModelError)
   {
       $result->logError( __file__, __line__ );
   }
   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Geocontexter\Model\BackupXmlReader;

class ListImport extends BackupXmlReader
{
    /**
     * import
     * @param array $params
     * @return bool true on success - object model error instance on error
     */
    public function import( $params = array() )
    {
        try {

            $this->openFile( $params['file'] );

            $this->beginTransaction();

            // read xml and import
            //
            $this->xml_read();

            $this->gc_xxx_seq_set_last_value();

            $this->rebuild_gc_list_index();

            $this->commit();

            $this->query('VACUUM ANALYZE geocontexter.gc_list_index');

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * this methode is called by the extended BackupXmlReader class
     *
     * @param array $row Row to insert in db table
     */
    protected function insert( $row )
    {
        // check if list parent exists
        // if not set id_parent to 0
        //
        if(($this->_table_name == 'gc_list') && ($row['id_parent'] != 0)) {
            if(false === $this->check_list_parent($row['id_parent'])) {
                $row['id_parent'] = 0;
            }
        }

        $this->insert($this->_table_name, 'geocontexter', $row);
    }

    /**
     * check if list parent exists
     *
     * @param string $id_parent
     */
    private function check_list_parent( $id_parent )
    {
        $id_list = $this->query('SELECT id_list FROM  geocontexter.gc_list WHERE id_list = ?', array($id_parent));

        if (false === $id_list) {
          return false;
        }

        return true;
    }

    /**
     * correct sequences last value
     *
     */
    private function gc_xxx_seq_set_last_value()
    {
        // If we import in an installation which has the same namespace and application id
        // the import run into conflict when the imported id_xxx is higher than
        // the existing sequence. Therefore we need to correct the sequence number
        //
        $sql = array('SELECT geocontexter.gc_list_seq_set_last_value()',
                     'SELECT geocontexter.gc_list_item_seq_set_last_value()',
                     'SELECT geocontexter.gc_list_keyword_seq_set_last_value()',
                     'SELECT geocontexter.gc_list_item_keyword_seq_set_last_value()',
                     'SELECT geocontexter.gc_item_seq_set_last_value()',
                     'SELECT geocontexter.gc_item_keyword_seq_set_last_value()',
                     'SELECT geocontexter.gc_attribute_seq_set_last_value()',
                     'SELECT geocontexter.gc_attribute_group_seq_set_last_value()',
                     'SELECT geocontexter.gc_keyword_seq_set_last_value()');

        foreach($sql as $query) {
            $this->query($query);
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
