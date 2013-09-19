<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Edit attribute
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */


namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class AttributeEditController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/attribute-edit/index.phtml' );

        // init view variables
        //
        $this->view->default_display         = '0';
        //$this->view->attribute_result      = array();
        $this->view->attribute_group_name    = array();
        $this->view->attribute_name          = '';
        $this->view->attribute_title         = '';
        $this->view->attribute_description   = '';
        $this->view->attribute_type          = '';
        $this->view->attribute_regex         = '';
        $this->view->attribute_unit          = '';
        $this->view->attribute_html_type     = '';
        $this->view->error                   = array();
    }

    public function indexAction()
    {
        $this->view->id_group = $this->id_group = $this->params()->fromRoute('id_group',false);

        if ($this->id_group === null) {
            return $this->error( 'no id_group request parameter defined.', __file__, __line__ );
        }

        $this->view->id_attribute = $this->id_attribute = $this->params()->fromRoute('id_attribute',false);

        if ($this->id_attribute === null) {
            return $this->error( 'no id_attribute request parameter defined.', __file__, __line__ );
        }

        $this->view->partialData = array('active_page' => 'attradd',
                                         'id_group'    => $this->id_group);

        $this->fetch_data();

        //$this->view->attribute_types = $this->get_attribute_types( $this->id_attribute, $this->view->attribute_result['attribute_type'] );

        $this->renderer->headTitle('Edit attribute: '.$this->view->attribute_result['attribute_name']);

        return $this->view;
    }

    /**
     * fetch attribute group and attribute
     */
    private function fetch_data()
    {
        $AttributeGroupGet = $this->CoreModel('AttributeGroupGet');

        $params = array('id_group' => $this->id_group );

        $result  = $AttributeGroupGet->run( $params );

        $this->view->attribute_group_name = $result['title'];

        $AttributeGet = $this->CoreModel('AttributeGet');

        $params = array('id_attribute'  => $this->id_attribute,
                        'system_serial' => true) ;

        $result  = $AttributeGet->run( $params );

        $this->view->attribute_result = $result;
    }

    /**
     * return attribute types for displaying in form select box
     * @param bigint $id_attribute
     * @param string $attribute_type
     * @return array
     */
    private function get_attribute_html_type( $attribute_type )
    {
        switch($attribute_type)
        {
            case 'bool':
                return 'radio';
            case 'int':
            case 'float':
                return 'input';
        }
    }

    /**
     * update attribute
     */
    public function updateAction()
    {
        $this->view->id_group = $this->id_group = $this->request->getPost()->id_group;

        // check on cancel action
        //
        $cancel = $this->request->getPost()->cancel;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/attribute-group-attributes/index/id_group/' . $this->id_group);
        }

        $params  = array();
        $_attribute_result = array();
        $_error  = array();

        $_error_head_title  = '';

        $AttributeUpdate = $this->CoreModel('AttributeUpdate');

        $_attribute_result['default_display'] = $default_display = $this->request->getPost()->default_display;
        $_attribute_result['system_serial'] = $system_serial = $this->request->getPost()->system_serial;

        if ($default_display == '1') {
            $params['data']['default_display'] = 'true';
        } else {
            $params['data']['default_display'] = 'false';
        }

        $_attribute_result['multi_value'] = $multi_value = $this->request->getPost()->multi_value;

        if ($multi_value == '1') {
            $params['data']['multi_value'] = 'true';
        } else {
            $params['data']['multi_value'] = 'false';
        }

        $this->view->id_attribute =
            $params['id_attribute'] = $this->request->getPost()->id_attribute;

        $_attribute_result['attribute_name'] =
            $params['data']['attribute_name'] = $this->request->getPost()->attribute_name;

        $_attribute_result['attribute_title'] =
            $params['data']['attribute_title'] = $this->request->getPost()->attribute_title;

        $_attribute_result['attribute_description'] =
            $params['data']['attribute_description'] = $this->request->getPost()->attribute_description;

        $_attribute_result['attribute_type'] =
            $params['data']['attribute_type'] = $this->request->getPost()->attribute_type;

        $_attribute_result['attribute_regex'] =
            $params['data']['attribute_regex'] = $this->request->getPost()->attribute_regex;

        $html_type = $this->request->getPost()->attribute_html_type;

        if ((null !== $html_type) && ($multi_value == '1')) {
            $this->view->attribute_result['attribute_html_type'] = $params['data']['attribute_html_type'] = 'textarea';
        }
        elseif ($params['data']['attribute_type'] == 'string') {
            $_attribute_result['attribute_html_type'] = $params['data']['attribute_html_type'] = $html_type;
        } else {
            $_attribute_result['attribute_html_type'] =  $params['data']['attribute_html_type'] = $this->get_attribute_html_type( $params['data']['attribute_type'] );
        }

        $_attribute_result['id_status'] =
            $params['data']['id_status'] = $this->request->getPost()->id_status;

        $this->view->attribute_result = $_attribute_result;

        if (empty($params['data']['attribute_name'])) {
            $_error[] = 'Attribute name is empty';
            $_error_head_title .= ' | Error: Attribute name is empty';
        }

        if (preg_match("/[^a-zA-Z0-9-_]/",$params['data']['attribute_name'])) {
            $_error[] = 'Attribute name field accept only the follwing chars: a-zA-Z0-9-_';
            $_error_head_title .= ' | Error: Attribute name field accept only the follwing chars: a-zA-Z0-9-_';
        }

        if (empty($params['data']['attribute_title'])) {
            $_error[] = 'Attribute title is empty';
            $_error_head_title .= ' | Error: Attribute title is empty';
        }

        if (!preg_match("/(bool|int|float|string)/",$params['data']['attribute_type'])) {
            $_error[] = 'Invalide attribute type';
            $_error_head_title .= ' | Error: Invalide attribute type';
        }

        if (count($_error) > 0) {
            $this->view->error = $_error;
            $this->view->headTitle($_error_head_title);
            return $this->view;
        }

        // update item attribute
        //
        $result = $AttributeUpdate->run( $params );

        $submit = $this->request->getPost()->submit;
        $update = $this->request->getPost()->update;

        if (isset($submit)) {
            $this->redirect()->toUrl($this->getAdminBaseUrl() . '/attribute-group-attributes/index/id_group/' . $this->id_group);
        }
        elseif (isset($update)) {
            return $this->view;
        }

    }

}

