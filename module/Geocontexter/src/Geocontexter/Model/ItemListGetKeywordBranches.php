<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get item list related keyword branches
 *
   USAGE:
   <pre>

   $ItemListGetKeywordBranches = $this->CoreModel('ItemListGetKeywordBranches');

   $params  = array('id_item' => bigint,
                    'id_list' => bigint);

   $ItemListGetKeywordBranches->run( $params );

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

class ItemListGetKeywordBranches extends    AbstractModel
                                 implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        $sql = 'SELECT  ilk.id_keyword,
                        (SELECT array_to_string(ARRAY(SELECT title FROM geocontexter.gc_keyword_get_branch(ilk.id_keyword)),\'/\') ) AS branch

                FROM geocontexter.gc_list_item AS il

                INNER JOIN geocontexter.gc_list_item_keyword AS ilk
                        ON il.id_list_item = ilk.id_list_item

                WHERE il.id_item = ?
                AND   il.id_list = ?

                ORDER BY branch';

        return $this->query($sql, array($params['id_item'], $params['id_list']));
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_item'])) {
            throw new \Exception('id_item field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_item'])) {
            throw new \Exception('id_item isnt from type bigint');
        }

        if (!isset($params['id_list'])) {
            throw new \Exception('id_list field isnt defined');
        }

        if (false === $val_digits->isValid($params['id_list'])) {
            throw new \Exception('id_list isnt from type bigint');
        }
    }
}
