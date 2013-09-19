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

   $ProjectGeometryGet = $this->CoreModel('ProjectGeometryGet');

   $params  = array('id_project'  => bigint);

   $result  = $ProjectGeometryGet->run( $params );

    if ($result instanceof \Core\Library\Exception) {
        return $this->error( $result->getMessage(), __file__, __line__);
    } else {
       $this->view->result = $result;
    }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (dim., 27 févr. 2011) $ / $LastChangedBy: armand.turpel $
  \*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectGeometryGet extends    AbstractModel
                         implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $sql = 'SELECT *,
                           ST_AsText(geom_polygon) AS txt_geometry,
                           geocontexter.ST_AsOpenLayersGeometry(geom_polygon) AS ol_geometry

                    FROM geocontexter.gc_project_geometry

                    WHERE  id_project = ' . $params['id_project'];

            return $this->query($sql);

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
        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_date->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}
