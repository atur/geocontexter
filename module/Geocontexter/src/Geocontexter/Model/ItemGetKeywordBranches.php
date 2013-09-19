<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get item related keyword branches
 *
   USAGE:
   <pre>

   $ItemGetKeywordBranches = $this->CoreModel('ItemGetKeywordBranches');

   $params  = array('id_item' => bigint);

   $result  = $ItemGetKeywordBranches->run( $params );

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

class ItemGetKeywordBranches extends AbstractModel
                             implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        if (!isset($params['id_item'])) {
            throw new \Exception('id_item field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_item'])) {
            throw new \Exception('id_item isnt from type bigint');
        }

        $sql = 'SELECT  id_keyword,
                        (SELECT array_to_string(ARRAY(SELECT title FROM geocontexter.gc_keyword_get_branch(id_keyword)),\'/\') ) AS branch
                FROM  geocontexter.gc_item_keyword
                WHERE id_item = ?
                ORDER BY branch';

        return $this->query($sql, array($params['id_item']));
    }
}
