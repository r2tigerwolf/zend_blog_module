<?php
namespace Blog\Controller;

use Blog\Service\PostServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Blog\Model\Blog;
use Blog\Model\Rating;
use Speak\Model\Connect;
use Upload\Model\Upload;
use Upload\Form\UploadForm;
use Blog\Form\BlogForm;
use Zend\Session\Container;

class IndexController extends AbstractActionController
{ 
    public $blogTable;
    public $ratingTable;
    public $connectTable;
	public $authenticateTable;
    public $uploadTable;
 
    public function userviewblogAction()
    {
        
        $session = new Container('User');
        $ownerId = (int)$this->getRequest()->getPost('ownerId');
        
        if (!$ownerId) {
            $ownerId = $session->user_id;
        } 

        $rowsetBlog = $this->getBlogTable()->fetchAllBlogs($ownerId);
        $rowset = array();
        
        foreach($rowsetBlog as $rowBlog) {
            $rowset[] =  array('id' => $rowBlog->id, 'title' => $rowBlog->title, 'body' => $rowBlog->body, 'date' => $rowBlog->date, 'ownerId' => $ownerId);
        }
        
        $viewModel = new ViewModel(array('rowset' => $rowset, 'ownerId' => $ownerId));
        return $viewModel;
    }
     
    public function viewblogAction()
    {
        $session = new Container('User');
        $ownerId = (int)$this->getRequest()->getPost('ownerId');
        if (!$ownerId) {
            $ownerId = $session->user_id;
        } 

        $rowsetBlog = $this->getBlogTable()->fetchAllBlogs($ownerId);
        $rowset = array();
        
        // Check if there's a connection before posting to another page
        $checkConnect = $this->getConnectTable()->checkConnectApproved($session->user_id, $session->owner_id);
        
        if($checkConnect == 1 || $session->user_id == $ownerId) {
            foreach($rowsetBlog as $rowBlog) {
                $rowset[] =  array('id' => $rowBlog->id, 'title' => $rowBlog->title, 'body' => $rowBlog->body, 'date' => $rowBlog->date);
            }
            
            $viewModel = new ViewModel(array('rowset' => $rowset));
            $viewModel->setTerminal(true);  // Prevents Header and Footer from being redered
            return $viewModel;
        }
        
        return false;
    }
    
    public function editblogAction()
    {
        $session = new Container('User');
        if($session->user_id) {
            $userId = $session->user_id;
            $blogId = $this->getRequest()->getPost('blogId');
    
    
            $rowsetBlog = $this->getBlogTable()->fetchOneBlog($userId, $blogId);
            $rowset = array();
            
            foreach($rowsetBlog as $rowBlog) {
                $rowset[] = array('id' => $rowBlog->id, 'title' => $rowBlog->title, 'body' => $rowBlog->body, 'date' => $rowBlog->date);
            }
            
            $viewModel = new ViewModel(array('rowset' => $rowset));
            return $viewModel;
        }
    }
	
    public function addblogAction() 
    {
        $session = new Container('User');  
        if($session->user_id) { 
            $blogId = (int)$this->getRequest()->getPost('id');
            $blogTitle = $this->getRequest()->getPost('title');
            $blogBody = $this->getRequest()->getPost('body');
            $userId = $session->user_id;
            $request = $this->getRequest();
            
            $rowset = array();
    
            $blogForm = new BlogForm();
            $blogForm->get('submit')->setValue('Submit');
            
            if ($request->isPost()) {
                 $blog = new Blog();
                 $blogForm->setInputFilter($blog->getInputFilter());
                 $blogForm->setData($request->getPost());
                 
                 if ($blogForm->isValid()) {
                     $blog->exchangeArray($blogForm->getData());
                     if($blogId) {
                        $this->getBlogTable()->updateBlog($userId, $blogId, $blogTitle, $blogBody);
                        $rowsetBlog = $this->getBlogTable()->fetchOneBlog($session->user_id, $blogId);
                     } else {
                        $lastInsertedId = $this->getBlogTable()->saveBlog($blog, $userId, $blogTitle, $blogBody);
                        $rowsetBlog = $this->getBlogTable()->fetchOneBlog($session->user_id, $lastInsertedId);
                     }
                     
                     foreach($rowsetBlog as $rowBlog) {
                        $rowset[] = array('id' => $rowBlog->id, 'title' => $rowBlog->title, 'body' => $rowBlog->body, 'date' => $rowBlog->date);
                     }
                 }
            } 
            $viewModel = new ViewModel(array('rowset' => $rowset));
            $viewModel->setTerminal(true);  // Prevents Header and Footer from being redered
            return $viewModel;
        }
    }
    
    public function deleteblogAction()
    {
        $session = new Container('User');
        if($session->user_id) {
            $id = (int)$this->getRequest()->getPost('id');
            $this->getBlogTable()->deleteBlog($id, $session->user_id);
        }
    }
    public function replyentryAction() 
    {
        $session = new Container('User');
        if($session->user_id) {
            if($session->owner_id == "") {
                $session->owner_id = $session->user_id; 
            }
            
            $form = new BlogForm();
            $form->get('submit')->setValue('Submit');
            $request = $this->getRequest();
            $parent = array();
            $child = array();
            $lastInsertedId = "";
                
            if ($request->isPost()) {
                 $blog = new Blog();
                 $form->setInputFilter($blog->getInputFilter());
                 $form->setData($request->getPost());
                 if ($form->isValid()) {
                     $blog->exchangeArray($form->getData());
                     $lastInsertedId = $this->getBlogTable()->saveBlog($blog, $session->user_id, $session->owner_id);
                 }
            } 
    
            $rowset = $this->getBlogTable()->fetchOneChild($lastInsertedId, $session->user_id, $session->owner_id); 
    
            foreach($rowset as $rowsetChild) {
            }
           
            $viewModel = new ViewModel(array('form' => $form,  'rowset' => $rowsetChild, 'lastInsertedId' => $lastInsertedId));
            return $viewModel;
        }
    }
    
    public function getBlogTable()
    {
        if(!$this->blogTable){
            $sm = $this->getServiceLocator();
            $this->blogTable = $sm->get("Blog\Model\BlogTable");
        }
        return $this->blogTable;
    }
    
    public function getRatingTable()
    {
        if(!$this->ratingTable){
            $sm = $this->getServiceLocator();
            $this->ratingTable = $sm->get("Blog\Model\RatingTable");
        }
        return $this->ratingTable;
    }
    
    public function getConnectTable()
    {
        if(!$this->connectTable){
            $sm = $this->getServiceLocator();
            $this->connectTable = $sm->get("Speak\Model\ConnectTable");
        }
        return $this->connectTable;
    }
    
    public function deleteAction()
    {
        $session = new Container('User');
        if($session->user_id) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $this->getRatingTable()->deleteRating($id, $session->user_id, $session->owner_id);
        }
    }
    
    public function addratingAction() 
    {
        $session = new Container('User');
        if($session->user_id) {
            $form = new BlogForm();
            $form->get('submit')->setValue('Submit');
            $ratingId = $this->getRequest()->getPost('id');
            //$userId = $this->getRequest()->getPost('user_id');
            $blogId = $this->getRequest()->getPost('blog_id');
            $replyBlogId = $this->getRequest()->getPost('reply_blog_id');
            $star = $this->getRequest()->getPost('star');
            
            $checkUserRating = $this->getRatingTable()->checkUserRating($blogId, $session->user_id, $session->owner_id);
            
            foreach($checkUserRating as $userRating) {
                $ratingMsg = '<p>&nbsp;</p><h3>You\'ve already ratedsss this entry a ' . $userRating->star . ' star</h3><p>&nbsp;</p>';
            }
            
            if($checkUserRating->count() == 0) {
                $this->getRatingTable()->saveRating($ratingId, $session->user_id, $session->owner_id, $blogId, $replyBlogId, $star);
                $ratingMsg = '<p>&nbsp;</p><h3>You\'ve rated this entry a ' . $star . ' star</h3><p>&nbsp;</p>';
            }
            
            $rowsetRating = $this->getRatingTable()->getRating($blogId, $session->user_id, $session->owner_id);
            
            foreach ($rowsetRating as $rowRating) {
                $rowset[] = array('id' => $rowRating->id, 'user_id' => $rowRating->user_id, 'blog_id' => $rowRating->blog_id, 'reply_blog_id' => $rowRating->reply_blog_id, 'star' => $rowRating->star);
                $sum += $rowRating->star;
                switch ($rowRating->star) {
                    case 1:
                        $star1+=1;
                        break;
                    case 2:
                        $star2+=2;
                        break;
                    case 3:
                        $star3+=3;
                        break;
                    case 4:
                        $star4+=4;
                        break;
                    case 5:
                        $star5+=5;
                        break;
                }
                
                $average = (($star1 * 1) + ($star2 * 2) + ($star3 * 3) + ($star4 * 4) + ($star5 * 5))/($sum);
            }
    
            $viewModel = new ViewModel(array('form' => $form,  'rowset' => $rowset, 'average' => $average, 'ratingMsg' => $ratingMsg));
            return $viewModel;
        }
    }
}