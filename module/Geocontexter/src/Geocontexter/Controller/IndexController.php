<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/geocontexter/
 * @package Geocontexter
 */

/**
 * Admin index controller
 *
 *
 * @package Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;


class IndexController extends AbstractController
{

    protected function init($controller)
    {
        $this->id_context = $this->params()->fromRoute('u',false);

    }

    protected function indexActionInit($controller)
    {
        $this->id_context = 12345;

    }

    public function indexAction()
    {

            //$e = $this->getEventManager();

//$id_context =$this->request->getParam('u');

       // $id_context = (int) $this->params()->fromRoute('id', 0);
       // die('<pre>'.var_export($this->id_context,true).'</pre>');
/*
        $mod = $this->CoreModel('TestModel');

        $result = $mod->run();

        if($result instanceof \Core\Library\Exception)
        {
        die ('<pre>'.var_export($result->getMessage(),true).'</pre>');
        }
        */
    }
}
