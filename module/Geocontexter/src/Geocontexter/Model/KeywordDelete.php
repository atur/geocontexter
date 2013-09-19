<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Delete keyword
 *
   USAGE:
   <pre>
   $keyword_delete = $this->CoreModel('KeywordDelete');

   $result  = $keyword_delete->run( array('id_keyword' => bigint) );

    if ($result instanceof \Core\Library\Exception) {
        return $this->error( $result->getMessage(), __file__, __line__);
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

class KeywordDelete extends    AbstractModel
                    implements InterfaceModel
{
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $this->delete('gc_keyword', 'geocontexter', array('id_keyword' => $params['id_keyword']));

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
    private function validate_params( & $params )
    {
        if(!isset($params['id_keyword']))
        {
            throw new \Exception('id_keyword field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if(false === $val_date->isValid($params['id_keyword']))
        {
            throw new \Exception('id_keyword isnt from type bigint');
        }

        
    }
}
