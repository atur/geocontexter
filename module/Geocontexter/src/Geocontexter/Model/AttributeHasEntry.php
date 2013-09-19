<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * check if attribute is associated with related entry content (return true or false)
 *
 *  USAGE:
   <pre>
    $attribute_has_entry = $this->CoreModel('AttributeHasEntry');

    $result  = $attribute_has_entry->run( array('id_attribute' => bigint) );

    if (false === $result) {
        // no entry
    } elseif (true === $result) {
        // one or more entry
    }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 837 $ / $LastChangedDate: 2011-03-17 11:24:28 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class AttributeHasEntry extends   AbstractModel
                                  implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        if (true !== ($is_valide = $this->validate_params( $params ))) {
            return $is_valide;
        }

        $sql = 'SELECT geocontexter.gc_attribute_has_relation(?) AS has_relation';

        $result = $this->query($sql, array($params['id_attribute']));

        if (isset($result[0]['has_relation'])) {
            return true;
        }

        return false;
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_attribute'])) {
            throw new \Exception('id_attribute isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_attribute'])) {
            throw new \Exception('id_attribute isnt from type bigint');
        }


    }
}
