<?php
/**
 * ZF2 Buch Kapitel 8
 *
 * Das Buch "Zend Framework 2 - Von den Grundlagen bis zur fertigen Anwendung"
 * von Ralf Eggert ist im Addison-Wesley Verlag erschienen.
 * ISBN 978-3-8273-2994-3
 *
 * @package    Pizza
 * @author     Ralf Eggert <r.eggert@travello.de>
 * @copyright  Alle Listings sind urheberrechtlich geschützt!
 * @link       http://www.zendframeworkbuch.de/ und http://www.awl.de/2994
 */

/**
 * namespace definition and usage
 */
namespace Geocontexter\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\Logger;
use Zend\Mvc\MvcEvent;

/**
 * Logging listener
 *
 * Listens on application level
 *
 * @package    Pizza
 */
class LoggingListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    private $exception_message = '';

    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @param  integer $priority
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'logErrors'), -100
        );
    }

    /**
     * Detach all our listeners from the event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * recursive loop through exceptions and assign messages
     *
     * @param object $e Exception
     */
    private function fetchExceptionMessage($e)
    {
        $this->exception_message .= $e->getCode() . ": ";
        $this->exception_message .= $e->getMessage() . "\n";
        $this->exception_message .= str_pad('-', 120, '-') . "\n";
        $this->exception_message .= $e->getFile() . " (";
        $this->exception_message .= $e->getLine() . ")\n";
        $this->exception_message .= str_pad('-', 120, '-') . "\n";
        $this->exception_message .= $e->getTraceAsString() . "\n";
        $this->exception_message .= str_pad('-', 120, '-') . "\n";

        if ( ($e = $e->getPrevious())) {
            $this->fetchExceptionMessage($e);
        }

        return;
    }

    /**
     * Log the errors
     *
     * @param  MvcEvent $e
     * @return null
     */
    public function logErrors(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $logger = $sm->get('\Geocontexter\Logging\Service');

        $exception = $e->getResult()->exception;

        if (!$exception) {
            return false;
        }

        $this->fetchExceptionMessage($exception);

        $logger->log(Logger::ERR, $this->exception_message);
    }
}
