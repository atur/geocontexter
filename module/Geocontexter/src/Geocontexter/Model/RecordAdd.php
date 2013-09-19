<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new record
 *
   USAGE:
   <pre>

   $RecordAdd = $this->CoreModel('RecordAdd');

   $params  = array('id_project'         => bigint,   //required
                    'id_context'         => bigint,   //required
                    'id_owner'           => bigint,   //required
                    'id_modifier'        => bigint,
                    'id_item'            => bigint,   //required
                    'id_attribute_group' => bigint,   //required
                    'id_status'          => int,      //required
                    'date_create'        => timestamp,
                    'update_time'        => timestamp,
                    'date_record_start'  => timestamp, //required
                    'date_record_end'    => timestamp,
                    'timezone'           => string,    //required
                    'geom_xxx'           => geometry,  // xxx = 'point' or 'linestring' or 'polygon'
                    'attribute_value'    => json,
                    'keywords'           => array);    // array of keywords strings in postgresql bigint format

   $result  = $RecordAdd->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
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

class RecordAdd extends    AbstractModel
                implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
  private $allowed_fields = array('id_project'             => true,
                                  'id_parent'              => true,
                                  'id_context'             => true,
                                  'id_owner'               => true,
                                  'id_modifier'            => true,
                                  'id_item'                => true,
                                  'id_attribute_group'     => true,
                                  'id_status'              => true,
                                  'date_create'            => true,
                                  'update_time'            => true,
                                  'date_record_start'      => true,
                                  'date_record_end'        => true,
                                  'timezone'               => true,
                                  'geom_point'             => true,
                                  'geom_linestring'        => true,
                                  'geom_polygon'           => true,
                                  'attribute_value'        => true);

    /**
     * add context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            if (!isset($params['date_create'])) {
                $params['date_create'] = 'NOW()';
            }

            if (!isset($params['update_time'])) {
                $params['update_time'] = 'NOW()';
            }

            $id_parent = 0;

            if (isset($params['id_parent'])) {
                $id_parent = $params['id_parent'];
                unset($params['id_parent']);
            }

            $params['date_record_start'] = new \Zend\Db\Sql\Expression("'{$params['date_record_start']}' AT TIME ZONE '{$params['timezone']}'");

            if (!isset($params['date_record_end'])) {
                $params['date_record_end'] = $params['date_record_start'];
            }

            if (isset($params['geom_point'])) {
                $_tmp_geom_point = new \Zend\Db\Sql\Expression($params['geom_point']);
                unset($params['geom_point']);
            } elseif(isset($params['geom_linestring'])) {
                $_tmp_geom_linestring = new \Zend\Db\Sql\Expression($params['geom_linestring']);
                unset($params['geom_linestring']);
            } elseif(isset($params['geom_polygon'])) {
                $_tmp_geom_polygon = new \Zend\Db\Sql\Expression($params['geom_polygon']);
                unset($params['geom_polygon']);
            }

            unset($params['timezone']);
            unset($params['attribute']);

            if (isset($params['keywords'])) {
                $__keywords = $params['keywords'];
                unset($params['keywords']);
            }

            $this->insert('gc_record', 'geocontexter', $params);

            $id_record = $this->query("SELECT currval('geocontexter.seq_gc_record') AS last_record");

            if (isset($_tmp_geom_point)) {
                $this->insert('gc_record', 'geocontexter', array('id_record'  => $id_record[0]['last_record'],
                                                                 'geom_point' => $_tmp_geom_point));
            } elseif(isset($_tmp_geom_linestring)) {
                $this->db->insert('gc_record', 'geocontexter', array('id_record'  => $id_record[0]['last_record'],
                                                                     'geom_point' => $_tmp_geom_linestring));
            } elseif(isset($_tmp_geom_polygon)) {
                $this->insert('gc_record', 'geocontexter', array('id_record'  => $id_record[0]['last_record'],
                                                                 'geom_point' => $_tmp_geom_polygon));
            }

            $this->insert('gc_record', 'geocontexter', array('id_record' => $id_record[0]['last_record'],
                                                             'id_parent' => $id_parent));

            if (isset($__keywords)) {

               $record_keyword = $this->CoreModel('RecordAddKeywords');

               $_params  = array('id_record'  => $id_record[0]['last_record'],
                                 'id_keyword' => $__keywords);

               $keyword_result  = $record_keyword->run( $_params );

               if ($keyword_result instanceof \Core\Library\Exception) {
                   throw new \Exception($keyword_result->getMessage());
               }
            }

            $this->commit();

            return $id_record[0]['last_record'];

        } catch(\Exception $e) {
            $this->rollback();
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
        foreach ($params as $key => $val) {
            if ((!isset($this->allowed_fields[$key])) && ($key != 'attribute') && ($key != 'keywords')) {
              throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (isset($params['attribute'])) {
            foreach ($params['attribute'] as $key => $val) {
              if (!isset($this->zend_db_expression[$key])) {
                throw new \Exception('Attribute field isnt allowed: ' . $key);
              }
            }
        }

        if (!isset($params['id_owner'])) {
            throw new \Exception('id_owner field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_owner'])) {
            throw new \Exception('id_owner isnt from type bigint');
        }

        if (isset($params['id_modifier'])) {
            if (false === $val_digits->isValid($params['id_modifier'])) {
                throw new \Exception('id_modifier isnt from type bigint');
            }
        }

        if (!isset($params['id_item'])) {
            throw new \Exception('id_item field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_item']))  {
            throw new \Exception('id_item isnt from type bigint');
        }

        if (!isset($params['id_context'])) {
            throw new \Exception('id_context field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_context'])) {
            throw new \Exception('id_context isnt from type bigint');
        }

        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }

        if (!isset($params['id_attribute_group'])) {
            throw new \Exception('id_attribute_group field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_attribute_group'])) {
            throw new \Exception('id_attribute_group isnt from type bigint');
        }

        if (!isset($params['id_status'])) {
            throw new \Exception('id_status field isnt defined');
        }

        $val_int = new \Zend\Validator\Int();

        if (false === $val_int->isValid($params['id_status'])) {
            throw new \Exception('id_status isnt from type int');
        }

        if (!isset($params['date_record_start'])) {
            throw new \Exception('date_record_start field isnt defined');
        }

        if (false === preg_match("/^([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}) ([0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})/",$params['date_record_start'])) {
            throw new \Exception('date_record_start isnt from type timestamp: ' . $params['date_record_start']);
        }

        if (isset($params['date_record_end'])) {
          if (false === preg_match("/^([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}) ([0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})/",$params['date_record_end'])) {
              throw new \Exception('date_record_end isnt from type timestamp: ' . $params['date_record_end']);
          }
        }

        if (!isset($params['timezone'])) {
            throw new \Exception('timezone field isnt defined');
        }

        $_timezones = DateTimeZone::listIdentifiers();
        foreach ($_timezones as $tz)  {
            if (strcasecmp($tz, $params['timezone'])==0) {
                $timezone_ok = true;
                break;
            }
        }

        if (!isset($timezone_ok)) {
            throw new \Exception('wrong timezone value: ' . $params['timezone']);
        }

        if (isset($params['keywords'])) {
            if (!is_array($params['keywords'])) {
                throw new \Exception('keyword parameter isnt from type array');
            } else {
              foreach ($params['keywords'] as $keyword) {
                  if (!is_string($keyword)) {
                      throw new \Exception('keyword in array isnt from type string: ' . var_export($keyword,true));
                  } elseif(false === $val_digits->isValid($keyword)) {
                      throw new \Exception('keyword in array isnt from postgresql type bigint: ' . var_export($keyword,true));
                  }
              }
            }
        }
    }
}