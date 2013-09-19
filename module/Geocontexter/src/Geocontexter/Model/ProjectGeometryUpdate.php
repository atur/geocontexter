<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geoLister/
 * @package GeoContexter
 */

/**
 * Update project geometry
 *
 *  USAGE:
   <pre>

    $ProjectGeometryUpdate = $this->CoreModel('ProjectGeometryUpdate');

   $params  = array('id_project_geometry' => bigint,   //required
                    'data'                => array('title'            => string,
                                                   'description'      => string,
                                                   'project_geometry' => string,
                                                   'srid'             => int));

   $result = $ProjectGeometryUpdate->run( $params );

    if ($result instanceof \Core\Library\Exception) {
        return $this->error( $result->getMessage(), __file__, __line__);
    }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (jeu., 17 mars 2011) $ / $LastChangedBy: armand.turpel $
\*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectGeometryUpdate extends    AbstractModel
                            implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('title'            => true,
                                    'description'      => true,
                                    'project_geometry' => true,
                                    'srid'             => true
                                    );

    /**
     * Update item attribute
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $this->beginTransaction();

            if (($params['data']['srid'] != 0) && ($params['data']['srid'] != $this->system_srid)) {
                $geom_object = "ST_Transform(ST_GeometryFromText('POLYGON(({$params['data']['project_geometry']}))',{$params['data']['srid']}), {$this->system_srid})";
            } else {
                $geom_object = "ST_GeometryFromText('POLYGON(({$params['data']['project_geometry']}))',{$this->system_srid})";
            }

            $params['data']['geom_polygon'] = new \Zend\Db\Sql\Expression($geom_object);

            $params['data']['original_geom_polygon'] = $params['data']['project_geometry'];
            $params['data']['original_geom_srid']    = $params['data']['srid'];

            unset($params['data']['srid']);
            unset($params['data']['project_geometry']);

            $params['data']['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");

            $this->update('gc_project_geometry', 'geocontexter',
                              $params['data'],
                              'id_project_geometry = ' . $params['id_project_geometry']);

            $this->commit();

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
        if (!isset($params['data'])) {
            throw new \Exception('data array isnt defined');
        }

        foreach ($params['data'] as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (!isset($params['id_project_geometry'])) {
            throw new \Exception('id_project_geometry field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_project_geometry'])) {
            throw new \Exception('id_project_geometry isnt from type bigint');
        }

        // we need the system srid to transform the new geometry if it has an other srid
        //
        $system = $this->CoreModel('SystemGet');

        $system_result  = $system->run();

        $this->system_srid = $system_result['srid'];

        if (isset($params['data']['srid']))  {

            $val_int = new \Zend\Validator\Int();

            if (false === $val_int->isValid($params['data']['srid'])) {
                throw new \Exception('srid isnt from type int');
            }
        } else {
            $params['data']['srid'] = $this->system_srid;
        }
    }
}
