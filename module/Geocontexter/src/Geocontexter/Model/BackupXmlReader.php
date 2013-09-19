<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Read Geocontexter database content xml file
 *
   USAGE:
   <pre>

   </pre>
 *
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class BackupXmlReader extends AbstractModel
{
    /**
     * open xml file
     *
     * @param string $file Export output file
     */
    protected function openFile( $file )
    {
        if (preg_match("/(.*)\.zip$/", $file['tmp_name'], $match)) {

            $filter     = new \Zend\Filter\Decompress(array(
              'adapter' => 'Zip',
              'options' => array(
                'archive' => $file['tmp_name'],
                'target' => GEOCONTEXTER_ROOT . '\data\tmp',
              )
            ));

            if (!$filter->filter($file['tmp_name'])) {
                throw new \Exception('Couldnt decompress upload archive: '.$file );
            }

            @unlink($file['tmp_name']);
        }

        $this->xml = new \XMLReader;

        if (false === $this->xml->open( GEOCONTEXTER_ROOT . '/data/tmp/' . str_replace(".zip","",$file['name']) )) {
            throw new \Exception('Couldnt read decompressed file: '.$file );
        }
    }

    protected function xml_read()
    {
        while($this->xml->read())
        {
            switch ($this->xml->nodeType)
            {
                case \XMLReader::END_ELEMENT:
                    if (($this->xml->name == 'field') ) {
                        $this->_field_num++;
                    } else if($this->xml->name == 'row') {
                        // insert row in db table
                        // methode must exists in the class which extends this class
                        //
                        if (false === $this->insert( $this->_row )) {
                            throw new \Exception('Couldnt import row: "'.var_export($this->_row,true));
                        }
                    }

                    break;
                case \XMLReader::ELEMENT:
                    if ($this->xml->name == 'geocontexter') {
                        $_namespace = $this->xml->getAttribute('namespace');
                        $_version   = $this->xml->getAttribute('version');

                        $this->validate($_version, $_namespace);
                    }

                    if ($this->xml->name == 'table') {
                        $this->_table_name = $this->xml->getAttribute('name');

                        $this->table_definition = $this->get_table_definition( $this->_table_name );

                        if (!isset($this->table_definition[0])) {
                            throw new \Exception('"'.$this->_table_name.'" for import dosent exists');
                        }
                    }

                    if ($this->xml->name == 'definition') {
                        $this->_field_name = array();
                    }

                    if ($this->xml->name == 'field_def') {
                        $this->_field_name[$this->xml->getAttribute('attr_num')] = $this->xml->getAttribute('field_name');
                        $this->type = $this->xml->getAttribute('field_type');
                        $this->notnull = $this->xml->getAttribute('notnull');
                    }

                    if ($this->xml->name == 'row') {
                        $this->_row = array();
                        $this->_field_num = 1;
                    }

                    if ($this->xml->isEmptyElement && ($this->xml->name == 'field')) {
                        if ($this->notnull == '') {
                            $this->_row[$this->_field_name[$this->_field_num]] = NULL;
                        } elseif($this->type == 'bool') {
                            $this->_row[$this->_field_name[$this->_field_num]] = 'f';
                        }
                        $this->_field_num++;
                    }

                    $this->xml_read();

                    break;
                case \XMLReader::TEXT:
                case \XMLReader::CDATA:
                    $this->_row[$this->_field_name[$this->_field_num]] = $this->xml->value;
            }
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
     * validate xml version and namespace
     *
     * @param string $version xml file version
     * @param string $namespace xml file namespace
     * @return bool
     */
    private function validate( $version, $namespace )
    {
        $system_get = $this->CoreModel('SystemGet');

        $result  = $system_get->run();

        if ($result[0]['system_version'] != $version) {
          throw new \Exception('Different system version: DB version: ' . $result[0]['system_version'] . ' - XML file version: ' . $version);
        }

        if ($result[0]['system_namespace'] != $namespace) {
          throw new \Exception('Different namespace: DB namespace: ' . $result[0]['system_namespace'] . ' - XML file namespace: ' . $namespace);
        }

        return true;
    }
}
