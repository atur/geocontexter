<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add item keyword relations
 *
   USAGE:
   <pre>
   $item_add_keywords = $this->CoreModel('ItemAddKeywords');

   $params  = array('id_item'    => bigint,
                    'id_keyword' => array(bigint, .., ..));

   $item_add_keywords->run( $params );

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

class ItemAddKeywords extends AbstractModel
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

            if (count($params['id_keyword']) == 0) {
                return;
            }

            $val_digits = new \Zend\Validator\Digits();

            foreach ($params['id_keyword'] as $keyword) {

                if (false === $val_digits->isValid($keyword)) {
                    throw new \Exception('keyword isnt from type bigint');

                // root not allowed
                //
                } else if($keyword == 0) {
                    continue;
                } else {

                    $this->insert('gc_item_keyword','geocontexter', array('id_keyword' => $keyword,
                                                                          'id_item'    => $params['id_item']));
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
        if (!isset($params['id_item']))  {
            throw new \Exception('id_item field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_item'])) {
            throw new \Exception('id_item isnt from type bigint');
        }

        if (!isset($params['id_keyword'])) {
            throw new \Exception('id_keyword field isnt defined');
        }

        if (!is_array($params['id_keyword'])) {
            throw new \Exception('id_keyword isnt from type array');
        }
    }
}
