<?php
 namespace Blog\Form;
 use Zend\Form\FormInterface;
 use Zend\Form\Form;

 class BlogForm extends Form
 {
     public function __construct($blog = null)
     {
         // we want to ignore the name passed
        parent::__construct('blog');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-ajax', 'false');
        $this->setAttribute('enctype','multipart/form-data');
        $this->setAttribute('data-transition','none');
        $this->setAttribute('data-clear-btn','true');
        $this->setAttribute('class','speakForm');
        $this->setAttribute('word-wrap','break-word');
        $this->setAttribute('resize','horizontal');
        $this->add(array(
             'name' => 'id',
             'type' => 'Hidden',
             'attributes' => array(
                'class' => 'blog-id',
             ),
        ));
        $this->add(array(
             'name' => 'user_id',
             'type' => 'Hidden',
        ));        
        $this->add(array(
             'name' => 'date',
             'type' => 'Hidden',
        ));

        $this->add(array(
            'name' => 'title',
            'id'   => 'blogTitle',
            'type' => 'text',
            'attributes' => array(
                'placeholder' => 'Title',
                'class' => 'blog-title',
            ),
        ));
        
        $this->add(array(
            'name' => 'body',
            'type' => 'Textarea',
            'attributes' => array(
                'id' => 'blogBody',
                'placeholder' => 'Speak your mind',
                'class' => 'blog-body',
                'data-clear-btn' => 'true',
            ),
        ));

        $this->add(array(
            'type' => 'Button',
            'name' => 'close',
            'options' => array(
            'label' => 'Close'),
            'attributes' => array(
                'id' => 'closebutton',         
                'data-toggle' => 'collapse',
                'data-target' => '.blog-row',
                'class' => 'btn btn-success ui-link btn-blog-close',
            ),
        ));
                                
        $this->add(array(
            'type' => 'Button',
            'name' => 'submit',
            'options' => array(
            'label' => 'Submit'),
            'attributes' => array(
                'id' => 'submitbutton',         
              
                'data-target' => '.blog-row',
                'class' => 'btn btn-success ui-link btn-blog-submit',
                'data-ajax' => 'false',                
            ),
        ));         
     }
 }
?>