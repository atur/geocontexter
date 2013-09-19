<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add project keyword relations
 *
   USAGE:
   <pre>

   $ProjectKeyword = $this->CoreModel('ProjectKeyword');

   $params  = array('id_project' => bigint,
                    'id_keyword' => array(bigint,.,.));

   $result  = $ProjectKeyword->run( $params );

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

class ProjectAddKeyword extends    AbstractModel
                        implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $val_digits = new \Zend\Validator\Digits();

            foreach ($params['id_keyword'] as $keyword) {

                if (false === $val_digits->isValid($keyword)) {
                    throw new \Exception('keyword isnt from type bigint: ' . var_export($keyword,true));

                // root not allowed
                //
                } else if($keyword == 0) {
                    continue;
                } else {

                    $this->insert('gc_project_keyword', 'geocontexter', array('id_keyword' => $keyword,
                                                                              'id_project' => $params['id_project']));
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
        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }

        if (!isset($params['id_keyword'])) {
            throw new \Exception('id_keyword field isnt defined');
        }

        if (!is_array($params['id_keyword'])) {
            throw new \Exception('id_keyword isnt from type array');
        }
    }
}
