<?php

namespace Core\Library;

use Zend\InputFilter;
use Zend\Form\Element;
use Zend\Form\Form;

class UploadForm
{
    /**
     * constructor
     *
     * @var $error array
     */
    public function __construct( $service )
    {
        $this->service  = $service;
        $this->form = new Form('upload-form');
    }

    public function init($name = null, $options = array())
    {
        if (null !== $name) {
            $this->form->setName($name);
        }

        if (!empty($options)) {
            $this->form->setOptions($options);
        }

        $this->addElements();
        $this->addInputFilter();
    }

    public function addElements()
    {
        // File Input
        $file = new Element\File('upload_file');
        $file->setAttribute('id', 'upload_file');
        $this->form->add($file);
    }

    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();

        // File Input
        $fileInput = new InputFilter\FileInput('upload_file');
        $fileInput->setRequired(true);
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => GEOCONTEXTER_ROOT . '/data/tmp/upload.zip',
                'randomize' => true,
            )
        );
        $inputFilter->add($fileInput);

        $this->form->setInputFilter($inputFilter);
    }

}
