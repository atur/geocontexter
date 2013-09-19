<?php
/**
 * Mozend
 * @link http://code.google.com/p/mozend/
 * @package Mozend
 */

/**
 * Action controller on which most module action controllers extends
 *
 *
 * @package GeocontexterCore
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class TestModel extends AbstractModel
{

    function run()
    {

       $result = $this->query('SELECT version()');

//return $result;

die(var_export($result,true));



    }

}