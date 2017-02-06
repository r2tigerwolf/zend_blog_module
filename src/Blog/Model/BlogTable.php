<?php
namespace Blog\Model;
 
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class BlogTable{
    // This table class does database operations using $tableGateway
    protected $tableGateway;
    protected $blogTable;
    public $select;
    // Service manager injects TableGateway object into this class
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function saveBlog(Blog $blog, $userId, $blogTitle, $blogBody)
    {
        $data = array(
            'id' => $blog->id,
            'user_id' =>  $userId,
            'title' => $blog->title,
            'body' =>  $blog->body,
            'date' =>  $blog->date 
        );

        $this->tableGateway->insert($data);
        $lastInsertedId = $this->tableGateway->lastInsertValue;
        return $lastInsertedId;
    }
    public function getBlogTable()
    {
        if (!$this->blogTable) {
        $sm = $this->getServiceLocator();
        $this->blogTable = $sm->get('Blog\Model\BlogTable');
        }
        return $this->blogTable;
    }
    
    
    public function fetchAllBlogs($userId)
    {
        $resultSet = $this->tableGateway->select(function (Select $select) use ($userId) {   
            $select->where(array('blog.user_id = \'' . $userId . '\''));
            $select->order('id ASC');
        });
        return $resultSet;
    }
    public function updateBlog($userId, $blogId, $blogTitle, $blogBody)
    {
        $this->tableGateway->update(array('title' => $blogTitle, 'body' => $blogBody), array('user_id = \'' . $userId . '\'', 'id = \'' . $blogId . '\''));  
    }
    
    public function fetchOneBlog($userId, $blogId)
    {
        $resultSet = $this->tableGateway->select(function (Select $select) use ($userId, $blogId) {   
            $select->where(array('blog.user_id = \'' . $userId . '\'', 'blog.id = \'' . $blogId . '\''));
            $select->order('id DESC');
        });
        return $resultSet;
    }
    
    public function deleteBlog($id, $userId)
    {
        $this->tableGateway->delete(array('id' => $id, 'user_id' => $userId));             
    }
}