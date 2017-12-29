<?php
require_once('src/AntLegacy/AntConfig.php');
require_once('src/AntLegacy/CDatabase.awp');
require_once('src/AntLegacy/AntUser.php');
require_once('src/AntLegacy/Ant.php');
require_once('src/AntLegacy/Controller.php');
require_once('src/AntLegacy/AntChat/SvrJson.php');

class ChatController extends Controller
{   
    public function __construct($ant, $user)
    {        
        $this->jsonChatSvr = new AntChat_SvrJson($ant, $user);
    }
    
    public function addFriend($params)
    {
        return $this->jsonChatSvr->addFriend($params);
    }
    
    public function deleteFriend($params)
    {
        return $this->jsonChatSvr->deleteFriend($params);
    }
    
    public function getFriendList($params)
    {
        return $this->jsonChatSvr->getFriendList($params);
    }
    
    public function getUserDetails()
    {
        return $this->jsonChatSvr->getUserDetails();
    }
        
    public function saveMessage($params)
    {
        return $this->jsonChatSvr->saveMessage($params);
    }
    
    public function getMessage($params)
    {
        return $this->jsonChatSvr->getMessage($params);
    }
    
    public function saveChatSession($params)
    {
        return $this->jsonChatSvr->saveChatSession($params);
    }
    
    public function clearChatSession($params)
    {
        return $this->jsonChatSvr->clearChatSession($params);
    }
    
    public function getChatSession($params)
    {
        return $this->jsonChatSvr->getChatSession($params);
    }
    
    public function getNewMessages($params)
    {
        return $this->jsonChatSvr->getNewMessages($params);
    }
    
    public function removeOldMessage($params)
    {
        return $this->jsonChatSvr->removeOldMessage($params);
    }
    
    public function countFriendOnline($params)
    {
        return $this->jsonChatSvr->countFriendOnline($params);
    }
    
    public function getPrevChat($params)
    {
        return $this->jsonChatSvr->getPrevChat($params);
    }
    
    public function processStatus($params)
    {
        return $this->jsonChatSvr->processStatus($params);
    }
}
