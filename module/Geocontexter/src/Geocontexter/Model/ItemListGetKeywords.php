<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get item list related keywords
 *
   USAGE:
   <pre>

   $ItemListGetKeywords = $this->CoreModel('ItemListGetKeywords');

   $params  = array('id_item'          => bigint,  // required
                    'id_list'          => bigint,  // limit to list id_list
                    'id_keyword_parent => bigint   // limit to keyword id_parent
                    );

   $result  = $ItemListGetKeywords->run( $params );

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

class ItemListGetKeywords extends    AbstractModel
                          implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        $sql = 'SELECT  k.*,il.id_list,il.id_list_item,
                        (SELECT array_to_string(ARRAY(SELECT title FROM geocontexter.gc_keyword_get_branch(ilk.id_keyword)),\'/\') ) AS branch

                FROM geocontexter.gc_list_item AS il

                INNER JOIN geocontexter.gc_list_item_keyword AS ilk
                        ON il.id_list_item = ilk.id_list_item

                INNER JOIN geocontexter.gc_keyword AS k
                        ON ilk.id_keyword = k.id_keyword

                WHERE il.id_item = ?

                '.$this->sql_list.'
                '.$this->sql_keyword_parent.'

                ORDER BY k.title';

        return $this->query($sql, array($params['id_item']));
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

        $this->sql_keyword_parent = '';

        if (isset($params['id_keyword_parent'])) {
            if (false === $val_digits->isValid($params['id_keyword_parent'])) {
                throw new \Exception('id_keyword_parent isnt from type bigint');
            } else {
                $this->sql_keyword_parent = 'AND k.id_parent = ' . $params['id_keyword_parent'];
            }
        }

        $this->sql_list = '';

        if (isset($params['id_list'])) {
            if (false === $val_digits->isValid($params['id_list'])) {
                throw new \Exception('id_list isnt from type bigint');
            } else {
                $this->sql_list = 'AND il.id_list = ' . $params['id_list'];
            }
        }
    }
}
