<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * render html form view with group attributes
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class AjaxGetGroupAttributesHtmlController extends AbstractController
{
    public function init()
    {
        // set view. we keep the index view instead of add
        //
        $this->initView( 'geocontexter/ajax-get-group-attributes-html/index.phtml' );
        $this->view->setTerminal(true);

    }

    /**
     * get group attributes
     */
    public function indexAction()
    {
        $this->view->id_group = $id_group = $this->request->getPost()->id_group;

        // get the item group attributes
        //
        $this->view->attribute_result = array();

        $AttributeGetGroupAttributes = $this->CoreModel('AttributeGetGroupAttributes');

        $params = array('id_group' => $id_group );

        $this->view->attribute_result = $AttributeGetGroupAttributes->run( $params );

        return $this->view;
    }
}

