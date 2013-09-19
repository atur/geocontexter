<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * change item synonym
 *
   USAGE:
   <pre>
   $ItemChangeSynonym = $this->CoreModel('ItemChangeSynonym');

   // if synonym_of is not defined, this means that the item has no synonym
   $params  = array('id_item'    => bigint,
                    'synonym_of' => bigint); // optional

   $ItemChangeSynonym->run( $params );

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

class ItemChangeSynonym extends    AbstractModel
                        implements InterfaceModel
{
    /**
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params( $params );

        $data = array();

        $data['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
        $data['synonym_of']  = $params['synonym_of'];

        $this->update('gc_item', 'geocontexter', $data, array('id_item' => $params['id_item']));
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

        if (isset($params['synonym_of'])) {
            if(false === $val_digits->isValid($params['synonym_of']))
            {
                throw new \Exception('synonym_of isnt from type bigint');
            }
        } else {
            $params['synonym_of'] = null;
        }
    }
}
