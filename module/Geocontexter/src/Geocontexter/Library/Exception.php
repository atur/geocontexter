<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/mozend/
 * @package Geocontexter
 */

/**
 * Class that handels exceptions
 *
 * @package Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Geocontexter\Library;

class Exception
{
    /**
     * error
     *
     * @access private
     * @var $error array
     */
    private $error = array();

    /**
     * constructor
     *
     * @var $error array
     */
    public function __construct( $service )
    {
        $this->service  = $service;
    }

    /**
     * register an exception
     *
     * @param object $e Exception
     * @param string $message
     * @return object This object
     */
    public function register( $e, $message = '' )
    {
        $this->exception($e);
        $this->exception_message .= "\n" . $message;
        $this->logException();
        return $this;
    }

    /**
     * recursive loop through exceptions and assign messages
     *
     * @param object $e Exception
     */
    private function exception($e)
    {
        $this->exception_message .= $e->getMessage()."\n";
        $this->exception_message .= $e->getTraceAsString()."\n";

        if ( ($e = $e->getPrevious())) {
            $this->exception($e);
        }

        return;
    }

    /**
     * Get the error as array
     *
     * @return array
     */
    public function getErrorArray()
    {
        return $this->error;
    }

    /**
     * Get the error as string
     *
     * @return string
     */
    public function getErrorString()
    {
        return var_export($this->error, true);
    }

    /**
     * log the exception
     *
     */
    private function logException()
    {
        $this->service->get('GcErrorLogger')->info($this->exception_message);
    }

    /**
     * recursive loop through exceptions and assign messages
     *
     * @return string Message depending of the application status (production or development)
     */
    public function getMessage()
    {
        $config = $this->service->get('Config');

        if ($config['error_handling'] == 0) {
            return $this->exception_message;
        } else {
            return $config['production_error_message'];
        }
    }
}
