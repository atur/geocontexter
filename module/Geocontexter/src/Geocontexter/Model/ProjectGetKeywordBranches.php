<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get project related keyword branches
 *
   USAGE:
   <pre>

   $ProjectGetKeywordBranches = $this->CoreModel('ProjectGetKeywordBranches');

   $params  = array('id_project' => bigint);

   $result  = $ProjectGetKeywordBranches->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 732 $ / $LastChangedDate: 2010-11-04 18:16:50 +0100 (jeu., 04 nov. 2010) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ProjectGetKeywordBranches extends    AbstractModel
                                implements InterfaceModel
{
    /**
     * update list
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $sql = 'SELECT  id_keyword,
                            (SELECT array_to_string(ARRAY(SELECT title FROM geocontexter.gc_keyword_get_branch(id_keyword)),\'/\') ) AS branch
                    FROM  geocontexter.gc_project_keyword
                    WHERE id_project = ?
                    ORDER BY branch';

            return $this->query($sql, array($params['id_project']));

        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( $params )
    {
        if (!isset($params['id_project'])) {
            throw new \Exception('id_project field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}
