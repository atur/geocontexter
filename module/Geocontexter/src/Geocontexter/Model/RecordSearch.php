<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * spatial search for records
 *
   USAGE:
   <pre>
   // tested with postgis 1.4.0
   //

   $RecordSearch = $this->CoreModel('RecordSearch');

   $params  = array('geom'        => string,           // ex.: ST_GeometryFromText('POINT(6.4 49.5)',4326)
                    'projects'    => array of bigints, // Limit search to id_projects
                    'items'       => array of bigints, // Limit search to id_items
                    'status'      => array('=', 1000),
                    'date_from'   => string,
                    'date_to'     => string,
                    'buffer'      => numeric,
                    // get only custom attributes which have default_display = true (1)
                    'default_display' => bool,

                    // if set, the function dont include an array var "attributes"
                    'no_transform_attributes' = bool,
                    'limit'       => array((int) limit,(int) offset));

   $result  = $RecordSearch->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = $result;
       $totalNumRows = $RecordSearch->totalNumRows();
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 896 $ / $LastChangedDate: 2011-08-14 15:30:16 +0200 (So, 14 Aug 2011) $ / $LastChangedBy: armand.turpel@gmail.com $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class RecordSearch extends    AbstractModel
                   implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            // check on preferred lists only
            //
            $_default_display = null;
            if (isset($params['default_display'])) {
                $_default_display = $params['default_display'];
            }

            // check on preferred lists only
            //
            $_no_transform_attributes = false;
            if (isset($params['no_transform_attributes'])) {
                $_no_transform_attributes = true;
            }

            $sql = 'SELECT rec.*,
                           geocontexter.ST_AsOpenLayersGeometry('.$this->sql_geom_column.') AS ol_geometry

                    FROM       geocontexter.gc_record AS rec

                    INNER JOIN geocontexter.gc_record_geometry AS recg
                            ON recg.id_record = rec.id_record

                    ' . $this->sql_gc_item_join . '

                    WHERE  ST_DWithin(' . $this->sql_geom_column . ',' . $this->geom . ', ' . $this->sql_buffer . ')

                     ' . $this->sql_id_project . '

                     ' . $this->sql_id_status . '

                     ' . $this->sql_id_item . '

                     ' . $this->sql_date_from . '

                     ' . $this->sql_date_to . '

                     ' . $this->sql_limit;

            if (true === $_no_transform_attributes) {
                return $this->query($sql);
            }

            // json decode of attribute_values
            //

            $result = $this->query($sql);

            $attr_json = $this->CoreModel('AttributeJsonDecode');

            foreach ($result as & $res) {

                if (($res['id_attribute_group'] == 'NULL') || empty($res['attribute_value'])) {
                    continue;
                }

                $res['attributes'] = $attr_json->decode( $res['attribute_value'], $res['id_attribute_group'], $_default_display );

                if ($res['attributes'] instanceof \Core\Library\Exception) {
                    throw new \Exception($res['attributes']->getMessage());
                }
            }

            return $result;

        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * count total rows without limit
     *
     * @return int total num rows
     * @todo optimize the query
     */
    public function totalNumRows()
    {
        try {
            $sql = 'SELECT count(rec.id_record) AS num_records

                    FROM  geocontexter.gc_record AS rec

                    INNER JOIN geocontexter.gc_record_geometry AS recg
                          ON rec.id_record = recg.id_record

                    ' . $this->sql_gc_item_join . '

                    WHERE ST_DWithin('.$this->sql_geom_column.',' . $this->geom . ', ' . $this->sql_buffer . ')

                     ' . $this->sql_id_project . '

                     ' . $this->sql_id_status . '

                     ' . $this->sql_id_item . '

                     ' . $this->sql_date_from . '

                     ' . $this->sql_date_to . '
                  ';

            $result  $this->query($sql);
            return $result[0]['num_records'];

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
        $val_digits = new \Zend\Validator\Digits();

        if (!isset($params['geom'])){
            throw new \Exception('geom field isnt defined');
        } else {
            $this->geom = $params['geom'];
        }

        $this->sql_limit = '';
        if (isset($params['limit'])) {
            $_offset = '';

            if (!is_array($params['limit']))  {
                throw new \Exception('limit isnt from type array');
            } elseif(!isset($params['limit'][0])) {
                throw new \Exception('limit array index 1 not set');
            } elseif(!is_int($params['limit'][0])) {
                throw new \Exception('limit array index 1 isnt from type integer');
            } elseif(isset($params['limit'][1])) {
                if (!is_int($params['limit'][1])) {
                    throw new \Exception('limit array index 2 isnt from type integer');
                } else {
                    $_offset = ' OFFSET ' . $params['limit'][1];
                }
            }

            $this->sql_limit = 'LIMIT ' . $params['limit'][0] . $_offset;
        }

        $this->sql_id_status = '';
        if (isset($params['status'])) {
            if (!is_array($params['status'])) {
                throw new \Exception('status isnt from type array');
            } elseif(!isset($params['status'][0])) {
                throw new \Exception('status array index 1 not set');
            } elseif(!isset($params['status'][1])) {
                throw new \Exception('status array index 2 not set');
            } elseif(!in_array($params['status'][0],array('>','<','>=','<=','='))) {
                throw new \Exception('status array index 1 string not recognized');
            } elseif(!is_int($params['status'][1])) {
                throw new \Exception('status array index 2 not from type int');
            }

            $this->sql_id_status = 'AND rec.id_status ' . $params['status'][0] . $params['status'][1];
        }

        $this->sql_buffer =  0;
        if (isset($params['buffer']))  {
            $this->sql_buffer =  $params['buffer'];
        }

        $this->sql_id_project = '';
        if (isset($params['projects'])) {
            if (!is_array($params['projects'])) {
                throw new \Exception('projects isnt from type array');
            } else {
                $_projects = '';
                $comma  = '';

                foreach ($params['projects'] as $id_project) {
                    if (false === $val_digits->isValid($id_project)) {
                        throw new \Exception('id_project in item array isnt from type bigint: ' . var_export($id_project,true));
                    } else {
                        $_projects .= $comma . $id_project;
                        $comma  = ',';
                    }
                }
                $this->sql_id_project = ' AND rec.id_project IN (' . $_projects . ') ';
            }
        }

        $this->sql_id_item      = '';
        $this->sql_gc_item_join = '';

        if (isset($params['items'])) {

                $this->sql_gc_item_join = 'INNER JOIN geocontexter.gc_item_synonym_index AS isi ON isi.id_item_dest = rec.id_item';

                $_items = '';
                $comma  = '';

                foreach ($params['items'] as $id_item) {
                    if (false === $val_digits->isValid($id_item)) {
                        throw new \Exception('id_item in item array isnt from type bigint: ' . var_export($id_item,true));
                    } else {
                        $_items .= $comma . $id_item;
                        $comma  = ',';
                    }
                }

                $this->sql_id_item = ' AND isi.id_item_source IN (' . $_items . ') ';

        }

        $this->sql_date_to = '';

        if (isset($params['date_to'])) {
            if (false === preg_match("/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/",$params['date_to'])) {
                throw new \Exception('wrong date format (ex. 2010-09-24) for date_to: ' . $params['date_to']);
            } else {
                $this->sql_date_to = "AND rec.date_record_start <= '" . $params['date_to'] . "'";
            }
        }

        $this->sql_date_from = '';

        if (isset($params['date_from'])) {
            if (false === preg_match("/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/",$params['date_from']))  {
                throw new \Exception('wrong date format (ex. 2010-09-24) for date_from: ' . $params['date_from']);
            } else {
                $this->sql_date_from = "AND rec.date_record_start >= '" . $params['date_from'] . "'";
            }
        }

        if (isset($params['default_display'])) {
            if (false === is_bool($params['default_display'])) {
                throw new \Exception('default_display isnt from type boolean');
            }
        }

        if (isset($params['no_transform_attributes'])) {
            if(false === is_bool($params['no_transform_attributes']))
            {
                throw new \Exception('no_transform_attributes isnt from type boolean');
            }
        }

        if (isset($params['geometry_column'])) {
            if (false === in_array($params['geometry_column'], array('point', 'linestring', 'polygon'))) {
                throw new \Exception('geometry_type must be one of string: "point", "linestring", "polygon"');
            } else {
                $this->sql_geom_column = 'recg.geom_' . $params['geometry_column'];
            }
        } else {
            $this->sql_geom_column = 'ST_ConvexHull(ST_Collect(recg.geom_point, recg.geom_linestring, recg.geom_polygon))';
        }
    }
}