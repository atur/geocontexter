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
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;

class GcAuth implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $service)
    {
        $dbAdapter           = $service->get('\Zend\Db\Adapter\Adapter');

        $table  = new \Zend\Db\Sql\TableIdentifier('gc_user', 'geocontexter');

        $adapter = new AuthAdapter(  $dbAdapter,
                                     $table,
                                     'user_login',
                                     'user_password',
                                     'MD5(?) AND id_status < 200'
                  );

        $authService = new AuthenticationService();
        $authService->setAdapter($adapter);
        $authService->setStorage($service->get('\Geocontexter\Library\Storage'));

        return $authService;
    }
}
