<?php

namespace Netric\Controller;

use Netric\Console\Console;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Permissions\Dacl;
use Netric\Mvc\AbstractController;

/**
 * Controller for handling user authentication
 */
class AuthenticationController extends AbstractController
{
    /**
     * Override to allow anonymous users to access this controller for authentication
     *
     * @return \Netric\Permissions\Dacl
     */
    public function getAccessControlList()
    {
        $dacl = new Dacl();

        // By default allow everyone and let the controller handle authentication
        $dacl->allowEveryone();

        return $dacl;
    }

    /**
     * Authenticate a new user
     */
    public function getAuthenticateAction()
    {
        $username = $this->request->getParam("username");
        $password = $this->request->getParam("password");
        $account = $this->request->getParam("account");

        // Check if the request was sent as a json object
        if ($this->request->getParam('Content-Type') === 'application/json') {
            $body = json_decode($this->request->getBody(), true);
            $username = $body['username'];
            $password = $body['password'];
            $account = $body['account'];
        }

        if (!$username || !$password || !$account) {
            return $this->sendOutput(
                [
                    "result" => "FAIL",
                    "reason" => "username, password and account are required fields"
                ]
            );
        }

        // Get the authentication service and authenticate the credentials
        $sm = $this->getApplication()->getServiceManager();
        $authService = $sm->get(AuthenticationServiceFactory::class);
        $sessionStr = $authService->authenticate($username, $password, $account);

        // Assume failure
        $ret = [
            "result" => "FAIL",
            "reason" => $authService->getFailureReason(),
        ];

        // Return the status
        if ($sessionStr) {
            // Set cookie for non-app access such as server renders
            if (!Console::isConsole()) {
                setcookie('Authentication', $sessionStr, $authService->getExpiresTs(), '/');
            }

            // Return session token
            $identity = $authService->getIdentity();
            $ret = [
                "result" => "SUCCESS",
                "session_token" => $sessionStr,
                "user_id" => $identity->getUserId(),
                "account_id" => $identity->getAccountId(),
            ];
        }


        return $this->sendOutput($ret);
    }

    /**
     * Authenticate a new user - POST version
     */
    public function postAuthenticateAction()
    {
        return $this->getAuthenticateAction();
    }

    /**
     * Clear an identity and log out
     */
    public function getLogoutAction()
    {
        // Destroy any cookies
        $this->request->setParam("Authentication", null);
        if (!Console::isConsole()) {
            unset($_COOKIE['Authentication']);
            setcookie('Authentication', null, -1, '/');
        }

        return $this->sendOutput(["result" => "SUCCESS"]);
    }

    /**
     * POST pass-through for logout
     *
     *  @return array|string
     */
    public function postLogoutAction()
    {
        return $this->getLogoutAction();
    }

    /**
     * Check if a session is still valid
     *
     *  @return array|string
     */
    public function getCheckinAction()
    {
        $sm = $this->getApplication()->getServiceManager();
        $authService = $sm->get(AuthenticationServiceFactory::class);

        $ret = [
            "result" => ($authService->getIdentity()) ? "OK" : "FAIL"
        ];

        if (!Console::isConsole() && $ret['OK'] && !isset($_COOKIE['Authentication'])) {
            // Set the cookie for future requests to the server
            setcookie(
                'Authentication',
                $this->request->getParam('Authentication'),
                $authService->getExpiresTs(),
                '/'
            );
        } elseif (isset($_COOKIE['Authentication'])) {
            // Clear all cookies if check fails
            unset($_COOKIE['Authentication']);
            setcookie('Authentication', null, -1, '/');
        }

        return $this->sendOutput($ret);
    }

    /**
     * POST pass-through for checkin
     *
     * @return array|string
     */
    public function postCheckinAction()
    {
        return $this->getCheckinAction();
    }

    /**
     * Get all accounts associated with a domain and return the name and instance URL
     */
    public function postGetAccountsAction()
    {
        $email = $this->request->getParam("email");

        // Check if the request was sent as a json object
        if ($this->request->getParam('Content-Type') === 'application/json') {
            $body = json_decode($this->request->getBody(), true);
            $email = $body['email'];
        }

        // TODO: Figure out a way to authorize the requestor so that
        // a bot cannot use this endpoint to validate email addresses.

        $ret = [];

        if ($email) {
            $ret = $this->getApplication()->getAccountsByEmail($email);
        }

        return $this->sendOutput($ret);
    }

    /**
     * Get all accounts associated with a domain and return the name and instance URL
     */
    public function getGetAccountsAction()
    {
        return $this->postGetAccountsAction();
    }
}
