<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * add new attribute
 *
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class AttributeAddController extends AbstractController
{
    /**
     * set some view variables we use in index view
     */
    public function init()
    {
        $this->initView( 'geocontexter/attribute-add/index.phtml' );

        // init view variables
        //
        $this->view->default_display         = '0';
        $this->view->attribute_name          = '';
        $this->view->attribute_title         = '';
        $this->view->attribute_description   = '';
        $this->view->attribute_type          = '';
        $this->view->attribute_regex         = '';
        $this->view->attribute_unit          = '';
        $this->view->multi_value             = '';
        $this->view->attribute_group_result  = array();
        $this->view->error                   = array();


    }

    public function indexAction()
    {
        $this->view->id_group = $this->id_group = $this->params()->fromRoute('id_group',false);

        if ((null === $this->id_group) || (0 == $this->id_group)) {
           return $this->error( '0 or no id_group request parameter defined.', __file__, __line__ );
        }

        $this->setPartial();

        $this->get_attribute_group();
        return $this->view;
    }

    private function setPartial()
    {
        $this->view->partialData = array('active_page' => 'attradd',
                                         'id_group'    => $this->id_group);
    }

    /**
     * add new group action
     */
    public function addAction()
    {
        // check on cancel action
        //
        $cancel = $this->request->getPost()->cancel;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/attribute/index');
        }

        $this->view->id_group = $this->id_group = $this->request->getPost()->id_group;

        $this->setPartial();

        $this->get_attribute_group();

        $params             = array();
        $_attribute_result  = array();
        $_error             = array();
        $_error_head_title  = '';

        $this->view->default_display = $default_display = $this->request->getPost()->default_display;

        if ($default_display == '1') {
            $params['default_display'] = 'true';
        } else {
            $params['default_display'] = 'false';
        }

        $this->view->multi_value = $multi_value = $this->request->getPost()->multi_value;

        if ($multi_value == '1') {
            $params['multi_value'] = 'true';
        } else {
            $params['multi_value'] = 'false';
        }

        $this->view->attribute_name =
            $params['attribute_name'] = $this->request->getPost()->attribute_name;

        $this->view->attribute_title =
            $params['attribute_title'] = $this->request->getPost()->attribute_title;

        $this->view->attribute_description =
            $params['attribute_description'] = $this->request->getPost()->attribute_description;

        $this->view->attribute_type =
            $params['attribute_type'] = $this->request->getPost()->attribute_type;

        $this->view->attribute_regex =
            $params['attribute_regex'] = $this->request->getPost()->attribute_regex;

        $this->view->attribute_unit =
            $params['attribute_unit'] = $this->request->getPost()->attribute_unit;

        if (empty($params['attribute_name'])) {
            $_error[] = 'Attribute name is empty';
            $_error_head_title .= 'Error: Attribute name is empty';
        }

        if (preg_match("/[^a-zA-Z0-9-_]/",$params['attribute_name'])) {
            $_error[] = 'Attribute name field accept only the follwing chars: a-zA-Z0-9-_';
            $_error_head_title .= 'Error: Attribute name field accept only the follwing chars: a-zA-Z0-9-_';
        }

        if (empty($params['attribute_title'])) {
            $_error[] = 'Attribute title is empty';
            $_error_head_title .= 'Error: Attribute title is empty';
        }

        if (!preg_match("/(bool|int|float|string)/",$params['attribute_type'])) {
            $_error[] = 'Invalide attribute type';
            $_error_head_title .= 'Error: Invalide attribute type';
        }

        if (count($_error) > 0) {
            $this->view->error = $_error;
            $this->renderer->headTitle($_error_head_title);
            return $this->view;
        }

        $params['id_group'] = $this->id_group;

        // add attribute
        //
        $AttributeAdd = $this->CoreModel('AttributeAdd');

        $result = $AttributeAdd->run( $params );

        $this->redirect()->toUrl($this->getAdminBaseUrl() . '/attribute-group-attributes/index/id_group/' . $this->id_group);
    }

    private function get_attribute_group()
    {
        $AttributeGroupGet= $this->CoreModel('AttributeGroupGet');

        $params = array('id_group' => $this->id_group );

        $result  = $AttributeGroupGet->run( $params );

        $this->view->attribute_group_name = $result['title'];

        // prepend to page title
        //
        $this->renderer->headTitle('Add new attribute to group: '.$result['title']);
    }
}

