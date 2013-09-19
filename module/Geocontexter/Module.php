<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/geocontexter/
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Geocontexter;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{

    private $admin_token = 'secret';

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();

        $eventManager->attach('dispatch', array($this, 'loadConfiguration' ));
        $eventManager->attach('render', array($this, 'registerJsonStrategy' ), 100);

        $eventManager->attachAggregate(new \Geocontexter\Listener\LoggingListener());

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->initSessionHandler($e);
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Geocontexter\Logging\Service' => function ($serviceManager) {

                    $config = $serviceManager->get('Config');
                    $logFile = $config['error_log_file'];

                    $writer = new \Zend\Log\Writer\Stream($logFile);
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter($writer);

                    return $logger;
                },
            ),
        );
    }

    public function registerJsonStrategy(MvcEvent $e)
    {
        // check controller
        $controller = $e->getRouteMatch()->getParam('controller');

        if (false === stristr($controller, 'Json')) {
            return;
        }

        // get event manager
        $serviceManager = $e->getApplication()->getServiceManager();

        // get view and json strategy
        $view         = $serviceManager->get('Zend\View\View');
        $jsonStrategy = $serviceManager->get('ViewJsonStrategy');

        // attach json strategy
        $view->getEventManager()->attach($jsonStrategy, 100);
    }

    public function loadConfiguration(MvcEvent $e)
    {
        $controller = $e->getRouteMatch()->getParam('controller');

        if (false !== stristr($controller, 'json')) {
            return;
        } else if (false !== stristr($controller, 'ajax')) {
            return;
        }

        $config = $e->getApplication()->getServiceManager()->get('Config');

        $controller = $e->getTarget();

        //set layout vars
        $controller->layout()->adminAreaToken = $config['adminAreaToken'];
        $controller->layout()->basePath       = $controller->getRequest()->getBasePath();

        //set view vars
        $children              = $controller->layout()->getChildren();
        $child                 = $children[0];
        $child->adminAreaToken = $this->admin_token;
        $child->basePath       = $controller->getRequest()->getBasePath();
    }

    private function initSessionHandler(MvcEvent $e)
    {
        $serviceManager     = $e->getApplication()->getServiceManager();
        $config             = $serviceManager->get('config');

        $table  = new \Zend\Db\Sql\TableIdentifier('gc_session', 'geocontexter');

        $sessionOptions = new \Zend\Session\SaveHandler\DbTableGatewayOptions($config['session']);

        $sessionTableGateway = new \Zend\Db\TableGateway\TableGateway($table, $serviceManager->get('db'));
        $saveHandler = new \Zend\Session\SaveHandler\DbTableGateway($sessionTableGateway, $sessionOptions);

        $sessionManager = new \Zend\Session\SessionManager();
        $sessionManager->setSaveHandler($saveHandler);
        \Zend\Session\Container::setDefaultManager($sessionManager);
        $sessionManager->start();
    }

    public function getConfig()
    {
        // set admin route
        //
        $config = include __DIR__ . '/config/module.config.php';
        $config['router']['routes']['admin']['options']['route'] = '/' . $this->admin_token . '[/:controller[/:action]]';
        return $config;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
