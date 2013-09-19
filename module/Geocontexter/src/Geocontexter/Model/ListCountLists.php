<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * lists counter
 *
   USAGE:
   <pre>

    $ListCountLists = $this->CoreModel('ListCountLists');

    $params  = array('limit' => int); // max lists to count // optionally

    $num_lists  = $ListCountLists->run( $params );

    if ($num_lists instanceof \Core\Library\Exception) {
        return $this->error( $num_lists->getMessage(), __file__, __line__);
    } else {
       $this->view->num_lists = $num_lists;
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

class ListCountLists extends    AbstractModel
                     implements InterfaceModel
{
    /**
     * count lists . optionally limit the results
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->validate_params($params);

            $_limit = '';
            if (isset($params['limit'])) {
                $_limit = 'LIMIT ' . $params['limit'];
            }

            $sql = 'SELECT  count(*) AS num_lists
                    FROM  geocontexter.gc_list
                    ' . $_limit;

            $_count = $this->query($sql);

            return $_count[0]['num_lists'];

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
        if (isset($params['limit'])) {

            $val_int = new \Zend\Validator\Int();

            if (false === $val_int->isValid($params['limit'])) {
                throw new \Exception('limit isnt from type int');
            }
        }

        
    }
}