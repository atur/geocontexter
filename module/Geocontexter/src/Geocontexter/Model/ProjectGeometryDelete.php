<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * delete project geometry
 *
 *
 *  USAGE:
   <pre>

   $ProjectGeometryDelete = $this->CoreModel('ProjectGeometryDelete');

   $params = array('id_project_geometry' => bigint);

   $result = $ProjectGeometryDelete->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }
   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (jeu., 17 mars 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectGeometryDelete extends    AbstractModel
                            implements InterfaceModel
{
    /**
     * delete attributes
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $this->beginTransaction();

            $this->delete('gc_project_geometry', 'geocontexter', array('id_project_geometry' => $params['id_project_geometry']));

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * validate parameters
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_project_geometry'])) {
            throw new \Exception('id_project_geometry field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_date->isValid($params['id_project_geometry'])) {
            throw new \Exception('id_project_geometry isnt from type bigint');
        }
    }
}