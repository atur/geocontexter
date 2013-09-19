<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get list related keyword branches
 *
   USAGE:
   <pre>

   $ListGetKeywordBranches = $this->CoreModel('ListGetKeywordBranches');

   $params  = array('id_list' => bigint);

   $result  = $ListGetKeywordBranches->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   } else {
       $this->view->result = & $result;
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

class ListGetKeywordBranches extends    AbstractModel
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
                    FROM  geocontexter.gc_list_keyword
                    WHERE id_list = ?
                    ORDER BY branch';

            return $this->query($sql, array($params['id_list']));

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
    private function validate_params( $params )
    {
        if (!isset($params['id_list'])) {
            throw new \Exception('id_list field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_list'])) {
            throw new \Exception('id_list isnt from type bigint');
        }
    }
}
