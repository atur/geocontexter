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

   $ProjectUpdate = $this->CoreModel('ProjectUpdate');

   $params  = array('id_project' => bigint id_project,
                    'data'    => array('title'              => string,
                                       'description'        => string,
                                       'id_parent'          => bigint,
                                       'id_context'         => bigint,
                                       'id_status'          => smallint,
                                       'date_project_start' => string,
                                       'date_project_end'   => string,
                                       'controller'         => string));

   $result = $ProjectUpdate->run( $params );

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

class ProjectUpdate extends    AbstractModel
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
                                    'id_owner'           => true,
                                    'id_status'          => true,
                                    'date_project_start' => true,
                                    'date_project_end'   => true,
                                    'controller'         => true,
                                    'lang'               => true
                                    );

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

            $params['data']['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");

            if (empty($params['data']['controller'])) {
                $params['data']['controller'] = null;
            }

            if (empty($params['data']['date_project_start'])) {
                $params['data']['date_project_start'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
            }

            if (empty($params['data']['date_project_end'])) {
                $params['data']['date_project_end'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
            }

            $this->update('gc_project', 'geocontexter', $params['data'], array('id_project' => $params['id_project']));

            if (isset($params['remove_project_list']) && is_array($params['remove_project_list'])) {
                $this->remove_project_lists( $params['id_project'], $params['remove_project_list'] );
            }

            if (isset($params['remove_id_keyword']) && (count($params['remove_id_keyword']) > 0)) {
                $this->remove_project_keywords( $params['id_project'], $params['remove_id_keyword'] );
            }

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * remove list keywords
     *
     *
     * @param string $id_list bigint
     * @param array $keywords
     */
    private function remove_project_keywords( $id_project, $keywords )
    {
        $val_digits = new \Zend\Validator\Digits();

        foreach ($keywords as $id_keyword) {
            if (false === $val_digits->isValid($id_keyword)) {
                throw new \Exception('id_keyword isnt from type bigint');
            }

            $this->delete('gc_project_keyword', 'geocontexter', array('id_keyword' => $id_keyword, 'id_project' => $id_project));
        }
    }

    /**
     * set and validate parameters
     *
     * @param array $params
     */
    private function remove_project_lists( $id_project, $lists )
    {
        foreach ($lists as $list) {
            $this->delete('gc_project_list', 'geocontexter', array('id_project' => $id_project, 'id_list' => $list));
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

        if (isset($params['data']['title'])) {
            if (empty($params['data']['title'])) {
                throw new \Exception('Project title is empty');
            }
        }

        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}
