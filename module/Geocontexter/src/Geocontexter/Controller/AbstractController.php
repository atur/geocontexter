<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/geocontexter/
 * @package Geocontexter
 */

/**
 * Basic abstract controller that every geocontexter module controller should extends
 *
 * @package Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Geocontexter\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\EventManager\EventManagerInterface;

abstract class AbstractController extends AbstractActionController
{
    private $modelFactory;

    /**
     * overwrite the setEventManager methode of the event manager
     *
     * call the init methode of the class (before the action logic) that extends this controller
     */
    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);

        $controller = $this;
        $events->attach('dispatch', function ($e) use ($controller) {

            // execute global class init methode
            //
            if (true === method_exists($controller, 'init')) {
                $this->init($controller);
            }

            // execute action init methode
            //
            $methode = $this->params()->fromRoute('action',false);
            $init_action = $methode . 'ActionInit';

            if (true === method_exists($controller, $init_action)) {
                $this->$init_action($controller);
            }

        }, 100); // execute before executing action logic
    }



    /**
     * build demanded model instance
     *
     * @param string $model_class Class name
     * @param string $namespace
     */
    protected function GcModel($model_class, $namespace = 'Geocontexter')
    {
        if (!isset($this->modelFactory)) {
            $this->modelFactory = $this->getServiceLocator()->get('GcModel');
        }
        return $this->modelFactory->getModelInstance($model_class, $namespace = 'Geocontexter');
    }

    /**
     * Action controller error handler
     *
     * @parem string $message
     * @parem string $file
     * @parem int $line
     */
    protected function error( $message, $file, $line, $log_only = false )
    {
        $_log_message  = "\nError in admin interface. \n";
        $_log_message .= "File: " . $file . "\n";
        $_log_message .= "Line: " . $line . "\n";
        $_log_message .= "Message: " . $message . "\n";
        $_log_message .= "_________________________________________________________\n";

        $this->logger->log($_log_message, Zend_Log::ERR);

        if($log_only === true)
        {
            return;
        }

        if ('development' == APPLICATION_ENV)
        {
            throw new Exception(str_replace("\n","<br />",$_log_message));
        }
        else
        {
            $this->_redirect($this->baseUrl . '/' . $this->view->adminAreaToken . '/geocontexter/error/simple',
                           array('exit' => true));
        }
    }
}
