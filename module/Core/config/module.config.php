<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'service_manager' => array(
        'factories' => array(
            'translator'         => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'CoreModel'          => 'Core\Service\CoreModelFactory',
            'CoreErrorLogger'    => 'Core\Service\CoreErrorLogger',
            'CoreUploadForm'     => 'Core\Service\CoreUploadForm',
            'CoreAuth'           => 'Core\Service\CoreAuth',
            'CoreSessionHandler' => 'Core\Service\CoreSessionHandler',
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
);
