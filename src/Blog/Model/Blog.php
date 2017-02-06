<?php
namespace Blog\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Blog implements InputFilterAwareInterface
{
	public $id;
    public $user_id;
    public $title;
    public $body;
    public $date;
    public $blog;
    public $ownerId;

    protected $inputFilter;  
        
	public function exchangeArray($data){ 
		$this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->title = (isset($data['title'])) ? $data['title'] : null;
        $this->body = (isset($data['body'])) ? $data['body'] : null;
        $this->date =  (isset($data['date'])) ? $data['date'] : null;
        $this->ownerId =  (isset($data['ownerId'])) ? $data['ownerId'] : null;
	}
    
      // Add content to these methods:
     public function setInputFilter(InputFilterInterface $inputFilter)
     {
        throw new \Exception("Not used");
     }
     
     public function getInputFilter()
     {
         if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            
            $inputFilter->add(array(
                 'name'     => 'id',
                 'required' => false,
                 'filters'  => array(
                     array('name' => 'Int'),
                 ),
            ));

            $inputFilter->add(array(
                 'name'     => 'title',
                 'required' => true,
                 'filters'  => array(
                     array('name' => 'StripTags'),
                     array('name' => 'StringTrim'),
                 ),
                 'validators' => array(
                     array(
                         'name'    => 'StringLength',
                         'options' => array(
                             'encoding' => 'UTF-8',
                             'min'      => 1,
                             'max'      => 1000,
                         ),
                     ),
                 ),
             ));
             
             $inputFilter->add(array(
                 'name'     => 'body',
                 'required' => true,
                 'filters'  => array(
                     //array('name' => 'StripTags'),
                     array('name' => 'StringTrim'),
                 ),
                 'validators' => array(
                     array(
                         'name'    => 'StringLength',
                         'options' => array(
                             'encoding' => 'UTF-8',
                             'min'      => 1,
                             'max'      => 65535,
                         ),
                     ),
                 ),
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}