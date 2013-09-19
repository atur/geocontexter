<?php
/**
 * Core
 * @link http://code.google.com/p/geocontexter/
 * @package Geocontexter
 */

/**
 * Basic abstract controller that every module controller should extends
 *
 * @package Core
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Core\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\EventManager\EventManagerInterface;

abstract class AbstractController extends AbstractActionController
{
    private $modelFactory;
    private $logged_user;
    private $session;

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

                // run authorisation
                //
                if (false === $controller->authorisation()) {
                
                    return $controller->redirect()->toRoute('admin', array('controller' => 'login'));
                }

                $controller->init($controller);
            }

            $controller->config = $e->getApplication()->getServiceManager()->get('Config');

            // execute action init methode
            //
            $methode = $controller->params()->fromRoute('action',false);
            $init_action = $methode . 'ActionInit';

            if (true === method_exists($controller, $init_action)) {
                $controller->$init_action($controller);
            }

        }, 100); // execute before executing action logic
    }

    /**
     * check authorisation
     *
     * @return bool true on success else false
     */
    public function authorisation()
    {
        $authorisation = $this->getServiceLocator()->get('CoreAuth');

        if (!$authorisation->hasIdentity()) {
            return false;
        }

        $this->logged_user = $authorisation->getStorage()->read();

        $this->initSession();

        return true;
    }

    protected function initView( $template )
    {
        $this->renderer     = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $this->view         = new \Zend\View\Model\ViewModel();
        $this->view->escape = new \Zend\Escaper\Escaper('utf-8');

        // read logged user data
        //
        $this->view->logged_user = $this->user = $this->get_identity();

        // get parent layout
        //
        $layout = $this->layout();

        // write view var with logged user data
        //
        $layout->logged_user = $this->user;

        $this->view->setTemplate($template);
    }

    protected function get_identity()
    {
        return $this->logged_user;
    }

    protected function getAdminBaseUrl()
    {
        return $this->baseurl = $this->getRequest()->getBasePath() . '/' .$this->config['adminAreaToken'];
    }

    protected function initSession()
    {
        $this->session = new \Zend\Session\Container('geocontexter');
    }

    protected function sessionOffsetGet($var_name)
    {
        return $this->session->offsetGet($var_name);
    }

    protected function sessionOffsetUnset($var_name)
    {
        return $this->session->offsetUnset($var_name);
    }

    protected function sessionOffsetSet($var_name, $var_content)
    {
        $this->session->offsetSet($var_name, $var_content);
    }

    protected function sessionOffsetExists($var_name)
    {
        return $this->session->offsetExists($var_name);
    }

    protected function sessionGet()
    {
        return $this->session;
    }

    /**
     * build demanded model instance
     *
     * @param string $model_class Class name
     * @param string $namespace
     */
    protected function CoreModel($model_class, $namespace = 'Geocontexter')
    {
        if (!isset($this->modelFactory)) {
            $this->modelFactory = $this->getServiceLocator()->get('CoreModel');
        }
        return $this->modelFactory->getModelInstance($model_class, $namespace = 'Geocontexter');
    }

    /**
     * error handler
     *
     * @parem string $message
     * @parem string $file
     * @parem int $line
     */
    protected function error($message, $file, $line)
    {
        $view = new \Zend\View\Model\ViewModel();
        $view->setTemplate('Error\Index.phtml');

        $_log_message  = "\nError in admin interface. \n";
        $_log_message .= "File: " . $file . "\n";
        $_log_message .= "Line: " . $line . "\n";
        $_log_message .= "Message: " . $message . "\n";
        $_log_message .= "_________________________________________________________\n";

        // log message
        //
        $this->getServiceLocator()->get('CoreErrorLogger')->info($_log_message);

        $config = $this->getServiceLocator()->get('Config');

        if ($config['error_handling'] == 0) {
            $view->message = $_log_message;
            return $view;
        } else {
            $view->message = $config['production_error_message'];
            return $view;
        }
    }
}
