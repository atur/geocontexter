<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Update project id_parent
 *
   USAGE:
   <pre>

   $ProjectUpdateParent = $this->CoreModel('ProjectUpdateParent');

   $params  = array('id_project' => bigint,
                    'id_parent'  => bigint);

   $result  = $ProjectUpdateParent->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
  \*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectUpdateParent extends    AbstractModel
                          implements InterfaceModel
{

    /**
     * update project
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $this->update('gc_project', 'geocontexter',
                              array('id_parent'   => $params['id_parent'],
                                    'update_time' => new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'")),
                              array('id_project' => $params['id_project']));

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
        if (!isset($params['id_parent'])) {
            throw new \Exception('id_parent field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_parent'])) {
            throw new \Exception('id_parent isnt from type bigint');
        }

        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}
