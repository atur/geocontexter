<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
   'router' => array(
        'routes' => array(
            'admin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/secret[/:controller[/:action]]',
                        /* OR add something like this to include the module path */
                        // 'route' => '/support[/:controller[/:action]]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Geocontexter\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                        'wildcard' => array(
                            'type' => 'Wildcard',
                                'options' => array(
                                    'key_value_delimiter' => '/',
                                    'param_delimiter' => '/',
                                ),
                        )
                )
            ),
        ),
    ),

    'service_manager' => array(
        'factories' => array(
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Geocontexter\Controller\AttributeGroupAttributes' => 'Geocontexter\Controller\AttributeGroupAttributesController',
            'Geocontexter\Controller\AttributeGroupEdit'       => 'Geocontexter\Controller\AttributeGroupEditController',
            'Geocontexter\Controller\AttributeGroupAdd'        => 'Geocontexter\Controller\AttributeGroupAddController',
            'Geocontexter\Controller\AttributeEdit'            => 'Geocontexter\Controller\AttributeEditController',
            'Geocontexter\Controller\AttributeAdd'             => 'Geocontexter\Controller\AttributeAddController',
            'Geocontexter\Controller\Attribute'                => 'Geocontexter\Controller\AttributeController',
            'Geocontexter\Controller\AttributeExportJson'      => 'Geocontexter\Controller\AttributeExportJsonController',
            'Geocontexter\Controller\List'                     => 'Geocontexter\Controller\ListController',
            'Geocontexter\Controller\ListAdd'                  => 'Geocontexter\Controller\ListAddController',
            'Geocontexter\Controller\ListEdit'                 => 'Geocontexter\Controller\ListEditController',
            'Geocontexter\Controller\ListSelect'                 => 'Geocontexter\Controller\ListSelectController',
            'Geocontexter\Controller\ItemAddNew'                 => 'Geocontexter\Controller\ItemAddNewController',
            'Geocontexter\Controller\ItemEdit'                   => 'Geocontexter\Controller\ItemEditController',
            'Geocontexter\Controller\ItemSearch'                 => 'Geocontexter\Controller\ItemSearchController',
            'Geocontexter\Controller\Project'                    => 'Geocontexter\Controller\ProjectController',
            'Geocontexter\Controller\ProjectAdd'                 => 'Geocontexter\Controller\ProjectAddController',
            'Geocontexter\Controller\ProjectEdit'                => 'Geocontexter\Controller\ProjectEditController',
            'Geocontexter\Controller\Keyword'                    => 'Geocontexter\Controller\KeywordController',
            'Geocontexter\Controller\KeywordAdd'                 => 'Geocontexter\Controller\KeywordAddController',
            'Geocontexter\Controller\KeywordEdit'                => 'Geocontexter\Controller\KeywordEditController',
            'Geocontexter\Controller\KeywordSelect'              => 'Geocontexter\Controller\KeywordSelectController',
            'Geocontexter\Controller\AjaxKeywordGetChildsHtml'   => 'Geocontexter\Controller\AjaxKeywordGetChildsHtmlController',
            'Geocontexter\Controller\AjaxListGetChildsHtml'      => 'Geocontexter\Controller\AjaxListGetChildsHtmlController',
            'Geocontexter\Controller\AjaxGetGroupAttributesHtml' => 'Geocontexter\Controller\AjaxGetGroupAttributesHtmlController',
            'Geocontexter\Controller\Index'                    => 'Geocontexter\Controller\IndexController',
            'Geocontexter\Controller\Login'                    => 'Geocontexter\Controller\LoginController',
            'Geocontexter\Controller\Context'                  => 'Geocontexter\Controller\ContextController',
            'Geocontexter\Controller\ContextAdd'               => 'Geocontexter\Controller\ContextAddController',
            'Geocontexter\Controller\ContextEdit'              => 'Geocontexter\Controller\ContextEditController',
        ),
    ),



    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'geocontexter/index/index' => __DIR__ . '/../view/geocontexter/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'paginator/control'       => __DIR__ . '/../view/paginator/control.phtml',
            'paginator-slide'         => __DIR__ . '/../view/partial/paginatorSlide.phtml',
            'item-search-slide'       => __DIR__ . '/../view/partial/itemSearchSlide.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
