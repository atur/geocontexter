<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Geocontexter database content xml file builder
 *
   USAGE:
   <pre>

      $XmlWriter = $this->CoreModel('XmlWriter');

      $XmlWriter->init((string) full file path , (string) system info );

      $XmlWriter->addTable((string) table name);

      $XmlWriter->addTableDefinition( (array) table definition );

      $XmlWriter->addRows( $db_result );

      $XmlWriter->endTable();

      $XmlWriter->end();

   </pre>
 *
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 878 $ / $LastChangedDate: 2011-07-30 09:30:41 +0200 (Sa, 30 Jul 2011) $ / $LastChangedBy: armand.turpel@gmail.com $
 */

namespace Geocontexter\Model;

class BackupXmlWriter extends \XMLWriter
{
    /**
     * constructor
     *
     * @param string $file Export output file
     * @param object $system System info
     */
    public function init( $file, $system )
    {
        $this->file = $file;

        $this->openURI($file);
        $this->startDocument('1.0', 'utf-8');
        $this->setIndent(2);

        $this->startElement('geocontexter');
        $this->writeAttribute('version', $system[0]['system_version']);
        $this->writeAttribute('namespace', $system[0]['system_namespace']);
        $this->writeAttribute('serial', $system[0]['system_serial']);
        date_default_timezone_set('UTC');
        $this->writeAttribute('export_date', date("Y-m-d h:i:s"));
        $this->writeAttribute('description', 'GeoContexter database content');
        $this->writeAttribute('link', 'http://code.google.com/p/geocontexter');
    }

    /**
     * add table
     *
     * @param string $name Table name
     */
    public function addTable( $name )
    {
            $this->startElement("table");
            $this->writeAttribute('name', $name);
    }

    /**
     * add table definition
     *
     * @param array $definition associated table definition array
     */
    public function addTableDefinition( $definition )
    {
        $this->startElement("definition");

        $this->_fields = array();

        foreach ($definition as $field) {

            $this->startElement('field_def');

            // store var related field type
            $this->_fields[$field['field_name']] = $field['field_type'];

            foreach ($field as $key => $val) {
                $this->writeAttribute($key, $val);
            }

            $this->endElement();
        }

        $this->endElement();
    }

    /**
     * add table row
     *
     * @param array $rows associated table row array
     */
    public function addRows( $rows )
    {
        foreach($rows as $row)
        {
            $this->startElement("row");
            foreach ($row as $key => $val) {
                if (($this->_fields[$key] == 'varchar') || ($this->_fields[$key] == 'text')) {
                    $this->startElement("field");
                    if (false == $this->writeCData($val)) {
                        throw new \Exception("Error on value: " . var_export($val, true));
                    }
                    $this->endElement();
                } else if(false == $this->writeElement('field', $val)) {
                    throw new \Exception("Error on value: " . var_export($val, true));
                }
            }
            $this->endElement();
        }
        return true;
    }

    /**
     * close table tag
     */
    public function endTable()
    {
        $this->endElement();
    }

    /**
     * close document tag
     */
    public function end()
    {
        $this->endDocument();
        $this->flush();
    }

    /**
     * compress export file
     * @return bool
     */
    public function compress()
    {
        $filter = new \Zend\Filter\Compress(array(
            'adapter' => 'Zip',
            'options' => array(
                'archive' => $this->file . '.zip',
            ),
        ));

        if ($filter->filter($this->file)) {
          @unlink($this->file);
          return true;
        }
        return false;
    }
}
