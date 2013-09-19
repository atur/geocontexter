<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new Module
 *
   USAGE:
   <pre>

   $ModuleAdd = $this->CoreModel('ModuleAdd');

   $params  = array('module_folder'      => string,
                    'module_title'       => string,
                    'module_version'     => string,
                    'module_date'        => timestamp,
                    'module_author'      => string,
                    'module_description' => string,
                    'module_url'         => string,
                    'module_repository'  => string);

   $result  = $ModuleAdd->run( $params );

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

class ModuleAdd extends    AbstractModel
                implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('module_folder'      => true,
                                    'module_title'       => true,
                                    'module_version'     => true,
                                    'module_date'        => true,
                                    'module_author'      => true,
                                    'module_description' => true,
                                    'module_url'         => true,
                                    'module_repository'  => true
                                    );

    /**
     * add List
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $params['module_rank'] = new \Zend\Db\Sql\Expression("geocontexter.gc_module_get_new_rank()");

            $this->insert('gc_module', 'geocontexter', $params);

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
        foreach ($params as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (!isset($params['module_folder'])) {
            throw new \Exception('module_folder field isnt defined');
        }

        if (empty($params['module_folder'])) {
            throw new \Exception('module_folder is empty');
        }

        if (!isset($params['module_title'])) {
            throw new \Exception('module_title field isnt defined');
        }

        if (empty($params['module_title'])) {
            throw new \Exception('module_title is empty');
        }

        if (!isset($params['module_version'])) {
            throw new \Exception('module_version field isnt defined');
        }

        if (empty($params['module_version'])) {
            throw new \Exception('module_version is empty');
        }
    }
}
