<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * check if attribute has related entry content
 *
   USAGE:
   <pre>
   $model_callback = new Geocontexter_Model_ModelCallback;

   // register model callback
   //
   $params = array('model_class'         => string,  // name of the model class
                   'model_class_methode' => string,  // methode name
                   'model_field'         => string,  // model field name to set/update
                   'id_name'             => string,  // name of the id of row to set/update
                   'id_value'            => bigint,  //  "" its corresponding value
                   'input_type'          => string,  // "checkbox" or "radio"
                   'check_circular'      => bool     // dont show item of "id_value"
        );

   $callback_number = $model_callback->register( $params );

   if($callback_number instanceof Mozend_ModelError)
   {
       $result->logError( __file__, __line__);
   }
   else
   {
    $this->view->callback_number = $callback_number;
   }

   //
   // run model callback
   //
   $params = array('callback_number' => int,     // number of the registered model callback
                   'value'           => mixed    // any value
        );

   $model_callback->run( $params );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
*/

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class ModelCallback extends    AbstractModel
{
    private $callback_number = 0;

    public $session;

    /**
     * @param array $params
        <pre>
        array('model_class'         => string,  // name of the model class
              'model_class_methode' => string,  // methode name
              'model_field'         => string,  // model field name to set/update
              'id_name'             => string,  // name of the id of row to set/update
              'id_value'            => bigint,  //  "" its corresponding value
              'input_type'          => string,  // "checkbox" or "radio"
              'check_circular'      => bool     // dont show item of "id_value"
        )
        </pre>
     * @param bool
     */
    public function register( $params )
    {
        $this->register_action = true;

        $this->validate_register( $params );

        $model_callback = $this->session->offsetGet('model_callback');
        $model_callback[$this->callback_number] = $params;
        $this->session->offsetSet('model_callback', $model_callback);
        return $this->callback_number++;
    }

    /**
     * @param array $params
     * @param bool
     */
    public function run( $__params )
    {
        if (!isset($__params['value'])) {
            throw new \Exception('value isnt defined');
        }

        if (!isset($__params['callback_number'])) {
            throw new \Exception('callback_number isnt defined');
        }

        $model_callback = $this->session->offsetGet('model_callback');

        if (!isset($model_callback[$__params['callback_number']])) {
            throw new \Exception('session model_number isnt defined');
        }

        if ($this->is_error() === true) {
            throw new \Exception( $this->get_error() );
        }

        $_model_class_name    = $model_callback[$__params['callback_number']]['model_class'];
        $_model_class_methode = $model_callback[$__params['callback_number']]['model_class_methode'];
        $_model_field         = $model_callback[$__params['callback_number']]['model_field'];

        $model = $this->CoreModel($_model_class_name);

        $params = array($model_callback[$__params['callback_number']]['id_name'] => $model_callback[$__params['callback_number']]['id_value']);

        $params[$model_callback[$__params['callback_number'][$_model_field]]] = $__params['value'];

        $result = $model->$_model_class_methode( $params );

        $this->view->close = true;

        // destruct action
        $model_callback[$__params['callback_number']] = null;
        $this->session->offsetSet('model_callback', $model_callback);

    }

    /**
     * get info about a callback model
     *
     * @param int $callback_number
     * @return array if it dosent exists false
     */
    public function get( $callback_number )
    {
        $model_callback = $this->session->offsetGet('model_callback');
        if (isset($model_callback[$callback_number])) {
            return $model_callback[$callback_number];
        }

        return false;
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_register( & $params )
    {
        if (!isset($params['input_type'])) {
            throw new \Exception('input_type isnt defined');
        } elseif (($params['input_type'] != 'radio') && ($params['input_type'] != 'checkbox')) {
            throw new \Exception('input_type value must be "radio" or "checkbox"');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (!isset($params['id_value'])) {
            throw new \Exception('id_value isnt defined');
        } elseif(false === $val_digits->isValid($params['id_value'])) {
            throw new \Exception('id_value isnt from type bigint');
        }

        if (!isset($params['id_name'])) {
            throw new \Exception('id_name isnt defined');
        }

        if (!isset($params['model_class'])) {
            throw new \Exception('model_class isnt defined');
        }

        if (!isset($params['model_class_methode'])) {
            throw new \Exception('model_class_methode isnt defined');
        }

        if (!isset($params['model_field'])) {
            throw new \Exception('model_field isnt defined');
        }
    }
}
