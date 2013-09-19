<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Update project
 *
   USAGE:
   <pre>

   $ProjectAddContext = $this->CoreModel('ProjectAddContext');

   $params  = array('id_project' => bigint,
                    'id_context' => bigint);

   $result  = $ProjectAddContext->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 888 $ / $LastChangedDate: 2011-08-10 15:05:16 +0200 (Mi, 10 Aug 2011) $ / $LastChangedBy: armand.turpel $
\*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectAddContext extends    AbstractModel
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

            $params['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");

            $this->update('gc_project', 'geocontexter', array('id_context'   => $params['id_context'],
                                                              'update_time'  => $params['update_time']),

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
        $data = array();

        if (!isset($params['id_context'])) {
            throw new \Exception('id_context field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_context'])) {
            throw new \Exception('id_context isnt from type bigint');
        }

        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}
