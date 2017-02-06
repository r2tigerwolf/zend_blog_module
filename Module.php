<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Blog;


use Blog\Model\Blog;
use Blog\Model\BlogTable;
use Blog\Model\Rating;
use Blog\Model\RatingTable;
use Blog\Model\Connect;
use Blog\Model\ConnectTable;
use Upload\Model\Upload;
use Upload\Model\UploadTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;

class Module
{
    public function initSession($config)
    {
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config);
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);
    }
    
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($event) {
            $controller      = $event->getTarget();
            $controllerName  = get_class($controller);
            $moduleNamespace = substr($controllerName, 0, strpos($controllerName, '\\'));
            $configs         = $event->getApplication()->getServiceManager()->get('config');
            if (isset($configs['moduleLayouts'][$moduleNamespace])) {
                $controller->layout($configs['moduleLayouts'][$moduleNamespace]);
            }
        }, 100);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
         return array(
             'factories' => array(
                 'Blog\Model\BlogTable' =>  function($sm) {
                     $tableGateway = $sm->get('BlogTableGateway');
                     $table = new BlogTable($tableGateway);
                     return $table;
                 },
                 'BlogTableGateway' => function ($sm) {
                     $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                     $resultSetPrototype = new ResultSet();
                     $resultSetPrototype->setArrayObjectPrototype(new Blog());
                     return new TableGateway('blog', $dbAdapter, null, $resultSetPrototype);
                 },
             ),
         );
     }
}
