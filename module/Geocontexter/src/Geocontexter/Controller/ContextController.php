<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Context main controller
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ContextController extends AbstractController
{
    /**
     * init the controller
     *
     * executed before any action logic
     */
    public function init()
    {
        $this->initView( 'geocontexter/context/index.phtml' );

        $this->id_context = $this->params()->fromRoute('id_context',false);

        if ((null === $this->id_context) || (0 == $this->id_context)) {
            $this->id_context = 0;
            $this->renderer->headTitle('Show child contexts of Root');
        }

        // read logged user data
        //
        $this->view->logged_user = $this->user = $this->get_identity();

        // get parent layout
        //
        $layout = $this->layout();

        // write view var with logged user data
        //
        $layout->logged_user = $this->user;

        $this->view->context_result        = array();
        $this->view->context_branch_result = array();
        $this->view->id_context            = $this->id_context;
        $this->view->error                 = array();

        // partial view variables for layout/context_navigation.phtml
        //
        $this->view->partialData = array('active_page'    => 'main',
                                         'id_context'     => $this->id_context);
    }

    public function indexAction()
    {
        // get current context if not root
        //
        if(0 != $this->id_context)
        {
            // get current context
            //
            $context = $this->CoreModel('ContextGet');

            $params                 = array('id_context' => $this->id_context);
            $result_current_context = $context->run( $params );

            if ($result_current_context === false) {
                throw new \Exception ('Context id dosent exists: ' . $this->id_context);
            }

            $this->renderer->headTitle('Show child contexts of ' . $result_current_context['title'], 'PREPEND');
        }

        // get branch of the current context
        //
        $context_branch = $this->CoreModel('ContextGetFromParentBranch');

        $params  = array('id_context' => $this->id_context) ;

        $this->view->context_branch_result = $context_branch->run( $params );

        // get childs of the current context
        //
        $context_childs = $this->CoreModel('ContextGetChilds');

        $params  = array('id_parent'     => $this->id_context,
                         'system_serial' => true) ;

        $this->view->context_result  = $context_childs->run( $params );

        return $this->view;
    }
}

