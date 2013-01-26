<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
	Router::connect('/', array('controller' => 'pages', 'action' => 'index'));
/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
	
	Router::connect('/profile/*', array('controller' => 'users', 'action' => 'publicProfile'));
	
	Router::connect('/members/profile-photo/*', array('controller' => 'users', 'action' => 'profilePhoto'));
	Router::connect('/members/basic-information/*', array('controller' => 'users', 'action' => 'basicInformation'));
	Router::connect('/members/contact-information/*', array('controller' => 'users', 'action' => 'contactInformation'));
	Router::connect('/members/location/*', array('controller' => 'users', 'action' => 'location'));
	Router::connect('/members/account/*', array('controller' => 'users', 'action' => 'account'));
    
    Router::connect('/members', array('controller' => 'users', 'action' => 'members'));
    Router::connect('/notifications/friend-requests', array('controller' => 'notifications', 'action' => 'friendRequests')); 
	Router::connect('/notifications/recent-updates', array('controller' => 'notifications', 'action' => 'recentUpdates')); 
	
    Router::connect('/whats-new/*', array('controller' => 'usersActivities', 'action' => 'whatsNew'));
 	Router::connect('/messages/inbox', array('controller' => 'usersMessages', 'action' => 'inbox'));
    Router::connect('/messages/inbox/read/*', array('controller' => 'usersMessages', 'action' => 'read'));
    Router::connect('/messages/sent', array('controller' => 'usersMessages', 'action' => 'sent'));
    Router::connect('/messages/deleted', array('controller' => 'usersMessages', 'action' => 'deleted'));
    Router::connect('/messages/compose', array('controller' => 'usersMessages', 'action' => 'compose'));
    
    Router::connect('/albums/add-new-photos', array('controller' => 'usersAlbums', 'action' => 'addNewPhotos'));
    Router::connect('/my-albums', array('controller' => 'usersAlbums', 'action' => 'myAlbums'));
    Router::connect('/albums/photo/gallery/*', array('controller' => 'usersAlbumsPhotos', 'action' => 'gallery'));
    Router::connect('/my-albums/manage-photos/*', array('controller' => 'usersAlbumsPhotos', 'action' => 'managePhotos'));
    Router::connect('/my-albums/edit-settings/*', array('controller' => 'usersAlbums', 'action' => 'editSettings'));
    Router::connect('/albums/browse/*', array('controller' => 'usersAlbums', 'action' => 'browseAlbums'));
	
    Router::connect('/blogs/browse/*', array('controller' => 'blogs', 'action' => 'browse'));
    Router::connect('/blogs/write-new/*', array('controller' => 'blogs', 'action' => 'writeNewEntry'));
    Router::connect('/blogs/my-entries/*', array('controller' => 'blogs', 'action' => 'myEntries'));
    Router::connect('/blogs/view-entry/*', array('controller' => 'blogs', 'action' => 'viewEntry'));
    
    Router::connect('/videos/search/*', array('controller' => 'usersVideos', 'action' => 'search'));  
    Router::connect('/videos/browse', array('controller' => 'usersVideos', 'action' => 'browse'));
    Router::connect('/videos', array('controller' => 'usersVideos', 'action' => 'index'));
    Router::connect('/videos/my-videos/*', array('controller' => 'usersVideos', 'action' => 'myVideos'));   
    Router::connect('/videos/manage-videos/*', array('controller' => 'usersVideos', 'action' => 'manageVideos'));  
    Router::connect('/videos/edit/*', array('controller' => 'usersVideos', 'action' => 'editVideo')); 
    Router::connect('/videos/add-category/*', array('controller' => 'usersVideosCategories', 'action' => 'addCategory'));
    Router::connect('/videos/edit-settings/*', array('controller' => 'usersVideosCategories', 'action' => 'editSettings'));
    Router::connect('/videos/play/*', array('controller' => 'usersVideos', 'action' => 'playVideo'));
    Router::connect('/videos/category-add/*', array('controller' => 'usersVideosCategories', 'action' => 'categoryAdd'));
    
	Router::connect('/session-expired', array('controller' => 'security', 'action' => 'sessionExpired'));	
   
   
/**
 * Load all plugin routes.  See the CakePlugin documentation on 
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
