<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new project geometry
 *
   USAGE:
   <pre>

   $ProjectGeometryAdd = $this->CoreModel('ProjectGeometryAdd');

   $params  = array('id_project'         => bigint,   //required
                    'title'              => string,
                    'description'        => string,
                    'project_geometry'   => string);  //required

   $result  = $ProjectGeometryAdd->run( $params );

    if ($result instanceof \Core\Library\Exception) {
        return $this->error( $result->getMessage(), __file__, __line__);
    }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (dim., 27 févr. 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectGeometryAdd extends    AbstractModel
                         implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_project'       => true,
                                    'title'            => true,
                                    'description'      => true,
                                    'project_geometry' => true,
                                    'srid'             => true);

    /**
     * add context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $this->beginTransaction();

            if (($params['srid'] != 0) && ($params['srid'] != $this->system_srid)) {
                $geom_object = "ST_Transform(ST_GeometryFromText('POLYGON(({$params['project_geometry']}))',{$params['srid']}), {$this->system_srid})";
            } else {
                $geom_object = "ST_GeometryFromText('POLYGON(({$params['project_geometry']}))',{$this->system_srid})";
            }

            $this->insert('gc_project_geometry', 'geocontexter',
                              array('id_project'            => $params['id_project'],
                                    'title'                 => $params['title'],
                                    'description'           => $params['description'],
                                    'geom_polygon'          => new \Zend\Db\Sql\Expression($geom_object),
                                    'original_geom_polygon' => $params['project_geometry'],
                                    'original_geom_srid'    => $params['srid'],
                                    'update_time'           => new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'")));

            $this->commit();

            $project_geometry_id = $this->query("SELECT currval('geocontexter.seq_gc_project_geometry') AS project_geometry_id");

            return $progect_geometry_id[0]['project_geometry_id'];

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

        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_date->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }

        // we need the system srid to transform the new geometry if it has an other srid
        //
        $system = $this->CoreModel('SystemGet');

        $system_result  = $system->run();

        $this->system_srid = $system_result['srid'];

        if (isset($params['srid'])) {

            $val_int = new \Zend\Validator\Int();

            if (false === $val_int->isValid($params['srid'])) {
                throw new \Exception('srid isnt from type int');
            }
        } else {
            $params['srid'] = $this->system_srid;
        }

        if (!isset($params['project_geometry'])) {
            throw new \Exception('project_geometry field isnt defined');
        }
    }
}