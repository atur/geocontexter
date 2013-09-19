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

namespace Core\Library;

class Exception
{
    /**
     * error
     *
     * @access private
     * @var exception_message string
     */
    private $exception_message = '';

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
     * get exception message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->exception_message;
    }
}
