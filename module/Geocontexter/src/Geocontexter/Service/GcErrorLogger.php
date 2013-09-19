<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/geocontexter/
 * @package Geocontexter
 */

/**
 * Error logger service
 *
 * @package Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Geocontexter\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GcErrorLogger implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $service)
    {
        $config = $service->get('Config');
        $log = new \Zend\Log\Logger();
        $writer = new \Zend\Log\Writer\Stream($config['error_log_file']);
        $log->addWriter($writer);

        return $log;
    }
}
