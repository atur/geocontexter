<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get project related keywords
 *
   USAGE:
   <pre>

   $ProjectGetKeywords = $this->CoreModel('ProjectGetKeywords');

   $params  = array('id_project' => bigint,  // required);

   $result  = $ProjectGetKeywords->run( $params );

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

class ProjectGetKeywords extends    AbstractModel
                         implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $sql = 'SELECT  k.*, pk.id_project_keyword,
                            (SELECT array_to_string(ARRAY(SELECT title FROM geocontexter.gc_keyword_get_branch(pk.id_keyword)),\'/\') ) AS branch

                    FROM geocontexter.gc_project_keyword AS pk

                    INNER JOIN geocontexter.gc_keyword AS k
                            ON pk.id_keyword = k.id_keyword

                    WHERE pk.id_project = ?

                    ORDER BY k.title';

            return $this->query($sql, array($params['id_project']));

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

        if (false === $val_digits->isValid($params['id_project'])) {
            throw new \Exception('id_project isnt from type bigint');
        }
    }
}
