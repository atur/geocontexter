<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * delete project keyword relations
 *
   USAGE:
   <pre>

   $ProjectDeleteKeyword = $this->CoreModel('ProjectDeleteKeyword');

   $params  = array('id_project_keyword' => array of bigints));

   $result  = $ProjectDeleteKeyword->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 732 $ / $LastChangedDate: 2010-11-04 18:16:50 +0100 (jeu., 04 nov. 2010) $ / $LastChangedBy: armand.turpel $
\*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectDeleteKeyword extends    AbstractModel
                           implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $this->beginTransaction();

            $val_digits = new \Zend\Validator\Digits();

            foreach ($params['id_project_keyword'] as $keyword)  {

                if (false === $val_date->isValid($keyword)) {
                    throw new \Exception('keyword isnt from type bigint');
                // root not allowed
                //
                } else if($keyword == 0) {
                    continue;
                } else {

                    $this->delete('gc_project_keyword', 'geocontexter', array('id_project_keyword' => $keyword));
                }
            }

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
        if (!isset($params['id_project_keyword'])) {
            throw new \Exception('id_project_keyword field isnt defined');
        }

        if (!is_array($params['id_project_keyword'])) {
            throw new \Exception('id_project_keyword isnt from type array');
        }
    }
}
