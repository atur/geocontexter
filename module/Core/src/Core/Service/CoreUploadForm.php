<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/geocontexter/
 * @package Geocontexter
 */

/**
 * Exception service
 *
 * @package Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Core\Service;

use \Core\Library\Uploadform;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CoreUploadForm implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $service)
    {
        return new UploadForm($service);
    }
}
