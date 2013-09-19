<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * spatial search for records and group the results by items
 *
   USAGE:
   <pre>
   // tested with postgis 1.4.0
   //
   $RecordSearchGroupByItems = $this->CoreModel('RecordSearchGroupByItems');

   $params  = array('geom'          => string,  // ex.: ST_GeometryFromText('POINT(6.4 49.5)',4326)
                    'id_project'    => bigint,
                    'date_from'     => string,
                    'date_to'       => string,
                    'in_item_list_id_keyword' => array(bigint,bigint, ...), // bigint as string
                    'subprojects'   => bool,
                    'status'        => array('=', 1000),
                    'buffer'        => numeric,
                    'limit'         => array((int) limit,(int) offset));

   $result  = $RecordSearchGroupByItems->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = $result;
       $totalNumRows = $RecordSearchGroupByItems->totalNumRows();
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

class RecordSearchGroupByItems extends    AbstractModel
                               implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);


            $_sql_limit = '';
            if (isset($params['limit'])) {
                $_sql_limit = 'LIMIT ' . $params['limit'][0] . ' OFFSET ' . $params['limit'][1];
            }

            // check on preferred lists only
            //
            $_default_display = null;
            i(isset($params['default_display'])) {
                $_default_display = $params['default_display'];
            }

            // check on preferred lists only
            //
            $_no_transform_attributes = false;
            i(isset($params['no_transform_attributes'])) {
                $_no_transform_attributes = true;
            }

            $sql = 'SELECT rec.id_item, item.title, count(DISTINCT rec.id_record) AS num_observations,
                           item.id_attribute_group,item.attribute_value,
                           geocontexter.gc_item_get_preferred_list_branch(rec.id_item) AS branch

                    FROM       geocontexter.gc_record AS rec

                    INNER JOIN geocontexter.gc_record_geometry AS recg
                          ON rec.id_record = recg.id_record

                    INNER JOIN geocontexter.gc_item   AS item
                            ON rec.id_item = item.id_item

                    ' . $this->sql_in_item_list_id_keyword_table . '

                    WHERE ST_DWithin(' . $this->sql_geom_column . ',' . $this->geom . ', ' . $this->sql_buffer . ')

                     ' . $this->sql_id_project . '

                     ' . $this->sql_id_status . '

                     ' . $this->sql_in_item_list_id_keyword . '

                     ' . $this->sql_date_from . '

                     ' . $this->sql_date_to . '

                     ' . $this->sql_alpha_filter . '

                    GROUP BY rec.id_item, item.title, branch,item.id_attribute_group,item.attribute_value
                    ORDER BY item.title ' . $_sql_limit;

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

            $this->validate_params($params);

            $sql = 'SELECT count(distinct(rec.id_item)) AS num_records
                    FROM  geocontexter.gc_record AS rec

                    INNER JOIN geocontexter.gc_record_geometry AS recg
                          ON rec.id_record = recg.id_record

                    INNER JOIN geocontexter.gc_item   AS item
                            ON rec.id_item = item.id_item

                    ' . $this->sql_in_item_list_id_keyword_table . '

                    WHERE ST_DWithin(' . $this->sql_geom_column . ',' . $this->geom . ', ' . $this->sql_buffer . ')

                     ' . $this->sql_id_project . '

                     ' . $this->sql_id_status . '

                     ' . $this->sql_in_item_list_id_keyword . '

                     ' . $this->sql_date_from . '

                     ' . $this->sql_date_to . '

                     ' . $this->sql_alpha_filter . '

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

        if (!isset($params['geom'])) {
            throw new \Exception('geom field isnt defined');
        } else {
            $this->geom = $params['geom'];
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
        if (isset($params['buffer'])) {
            $this->sql_buffer =  $params['buffer'];
        }

        $this->sql_id_project = '';
        if (isset($params['id_project'])) {
            if (false === $val_digits->isValid($params['id_project'])) {
                throw new \Exception('id_project isnt from type integer');
            } else {
                $this->sql_id_status = 'AND rec.id_project = ' . $params['id_project'];
            }
        }

        if (isset($params['default_display'])) {
            if (false === is_bool($params['default_display']))  {
                throw new \Exception('default_display isnt from type boolean');
            }
        }

        $this->sql_in_item_list_id_keyword = '';
        $this->sql_in_item_list_id_keyword_table = '';

        if (isset($params['in_item_list_id_keyword']) &&
            is_array($params['in_item_list_id_keyword']) &&
            (count($params['in_item_list_id_keyword']) > 0)) {

            $k_tmp = '';
            $comma = '';

            foreach ($params['in_item_list_id_keyword'] as $id_keyword) {
                if (false === $val_digits->isValid($id_keyword)) {
                    throw new \Exception('id_keyword in array "in_item_list_id_keyword" isnt from type bigint');
                } else {
                    $k_tmp .= $comma . $id_keyword;
                    $comma = ',';
                }
            }

            $this->sql_in_item_list_id_keyword       = 'AND gcilk.id_keyword IN('.$k_tmp.')';

            $this->sql_in_item_list_id_keyword_table = 'INNER JOIN geocontexter.gc_list_item AS gcil
                                                              ON gcil.id_item = item.id_item

                                                        INNER JOIN geocontexter.gc_list_item_keyword AS gcilk
                                                              ON gcilk.id_list_item = gcil.id_list_item';
        }

        if (isset($params['no_transform_attributes'])) {
            if (false === is_bool($params['no_transform_attributes'])) {
                throw new \Exception('no_transform_attributes isnt from type boolean');
            }
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
            if (false === preg_match("/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/",$params['date_from'])) {
                throw new \Exception('wrong date format (ex. 2010-09-24) for date_from: ' . $params['date_from']);
            } else {
                $this->sql_date_from = "AND rec.date_record_start >= '" . $params['date_from'] . "'";
            }
        }

        $this->sql_alpha_filter = '';

        if (isset($params['alpha_filter'])) {
            if (false === preg_match("/[A-Z]{1}/",$params['alpha_filter'])) {
                throw new \Exception('wrong alpha filter content: ' . $params['alpha_filter']);
            } else {
                $this->sql_alpha_filter = "AND item.title LIKE '" . $params['alpha_filter'] . "%'";
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