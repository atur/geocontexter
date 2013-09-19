<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * search for keywords
 *
   USAGE:
   <pre>

   $KeywordSearch = $this->CoreModel('KeywordSearch');

   $params  = array('search'          => string,
                    // get default custom attributes: 1 = get them, 2 = get non default, default = all
                    'default_attributes' => integer,
                   // 'system_serial' field is included in the result
                   // it contains the system serial if id_group is within the system serial
                   // else null
                   'system_serial' => bool); // optional , value: true

   $result  = $KeywordSearch->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = $result;
       $totalNumRows = $KeywordSearch->totalNumRows($params);

       if ($totalNumRows instanceof \Core\Library\Exception) {
           return $this->error( $totalNumRows->getMessage(), __file__, __line__);
       }
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

class KeywordSearch extends    AbstractModel
                    implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            // if the system_serial check must be included
            //
            $_system_serial = "";
            if(isset($params['system_serial']) && ($params['system_serial'] == true))
            {
                $_system_serial = ", geocontexter.gc_system_is_serial(gk.id_keyword) AS system_serial";
            }

            // check on preferred lists only
            //
            $_default_display = null;
            if(isset($params['default_display']))
            {
                $_default_display = $params['default_display'];
            }

            $_no_transform_attributes = false;
            if(isset($params['no_transform_attributes']))
            {
                $_no_transform_attributes = true;
            }

            $_sql_limit = '';
            if(isset($params['limit']))
            {
                $_sql_limit = 'LIMIT ' . $params['limit'][0] . ' OFFSET ' . $params['limit'][1];
            }

            $sql = 'SELECT  gk.*  '.$_system_serial.' ,
                            (SELECT array_to_string(ARRAY(SELECT title FROM geocontexter.gc_keyword_get_branch(gk.id_keyword)),\'/\') ) AS branch
                    FROM  geocontexter.gc_keyword AS gk
                    WHERE gk.title ILIKE ?
                    ORDER BY gk.title ' . $_sql_limit;

            if(true === $_no_transform_attributes)
            {
                return $this->query($sql, array($params['search'] . '%'));
            }

            $result = $this->query($sql, array($params['search'] . '%'));

            $AttributeJsonDecode = $this->CoreModel('AttributeJsonDecode');

            foreach($result as & $res)
            {
                if(($res['id_attribute_group'] == 'NULL') || empty($res['attribute_value']))
                {
                    continue;
                }

                $res['attributes'] = $AttributeJsonDecode->decode( $res['attribute_value'], $res['id_attribute_group'], $_default_display );

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
     * search keywords without limit for zend_paginator
     *
     *
     * @param array $params
     */
    public function totalNumRows($params)
    {
        try {

            $this->validate_params($params);

            $sql = 'SELECT  id_keyword
                    FROM  geocontexter.gc_keyword
                    WHERE title LIKE ?';

            $res = $this->query($sql, array($params['search'] . '%'));
            return (int)count($res);

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
        if (!isset($params['search'])) {
            throw new \Exception('search field isnt defined');
        }

        if (isset($params['default_display'])) {
            if (false === is_bool($params['default_display'])) {
                throw new \Exception('default_display isnt from type boolean');
            }
        }

        if (isset($params['no_transform_attributes'])) {
            if (false === is_bool($params['no_transform_attributes'])) {
                throw new \Exception('no_transform_attributes isnt from type boolean');
            }
        }

        
    }
}