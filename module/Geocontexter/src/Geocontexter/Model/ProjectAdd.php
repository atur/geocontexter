<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/geocontexter/
 * @package Geocontexter
 */

/**
 * Add new project
 *
   USAGE:
   <pre>

   $ProjectAdd = $this->CoreModel('ProjectAdd');

   $params['data']  = array('title'              => string,
                            'description'        => string,
                            'id_parent'          => bigint (string),
                            'id_context'         => bigint (string),
                            'id_status'          => smallint,
                            'controller'         => string,
                            'lang'               => string);

   $result  = $ProjectAdd->run( $params['data'] );

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

class ProjectAdd extends    AbstractModel
                 implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('title'              => true,
                                    'description'        => true,
                                    'id_parent'          => true,
                                    'id_context'         => true,
                                    'id_status'          => true,
                                    'controller'         => true,
                                    'lang'               => true
                                    );

    /**
     * add project
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $params['data']['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");

            $this->insert('gc_project', 'geocontexter', $params['data']);

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
        foreach ($params['data'] as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (!isset($params['data']['title'])) {
            throw new \Exception('project title field isnt defined');
        }

        if (empty($params['data']['title'])) {
            throw new \Exception('project title is empty');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (isset($params['data']['id_parent'])) {
            if (false === $val_digits->isValid($params['data']['id_parent'])) {
                throw new \Exception('id_parent isnt from type bigint');
            }
        }

        if (isset($params['data']['id_context'])) {
            if (false === $val_digits->isValid($params['data']['id_context'])) {
                throw new \Exception('id_context isnt from type bigint');
            }
        }
    }
}
