<?php

namespace Netric\Controller;

use Netric\Mvc;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Account\AccountContainerFactory;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\Application\Application;
use Netric\Console\Console;
use Netric\Permissions\Dacl;

/**
 * Controller for handling user authentication
 */
class AuthenticationController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Service used to get the current user/account
     */
    private AuthenticationService $authService;

    /**
     * The current application instance
     */
    private Application $application;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param Application $application The current application instance
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        Application $application
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->application = $application;
    }

    /**
     * Get the currently authenticated account
     *
     * @return Account
     */
    private function getAuthenticatedAccount()
    {
        $authIdentity = $this->authService->getIdentity();
        if (!$authIdentity) {
            return null;
        }

        return $this->accountContainer->loadById($authIdentity->getAccountId());
    }

    /**
     * Authenticate a new user
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getAuthenticateAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);
        $username = $request->getParam("username");
        $password = $request->getParam("password");
        $account = $request->getParam("account");

        // Check if the request was sent as a json object
        $rawBody = $request->getBody();
        if ($rawBody) {
            $body = json_decode($rawBody, true);
            $username = $body['username'];
            $password = $body['password'];
            $account = $body['account'];
        }

        if (!$username || !$password || !$account) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(
                [
                    "result" => "FAIL",
                    "reason" => "username, password and account are required fields."
                ]
            );
            return $response;
        }

        // Authenticate the credentials
        $sessionStr = $this->authService->authenticate($username, $password, $account);

        // Assume failure
        $ret = [
            "result" => "FAIL",
            "reason" => $this->authService->getFailureReason(),
        ];

        // Return the status
        if ($sessionStr) {
            // Set cookie for non-app access such as server renders
            if (!Console::isConsole()) {
                setcookie('Authentication', $sessionStr, $this->authService->getExpiresTs(), '/');
            }

            // Return session token
            $identity = $this->authService->getIdentity();
            $ret = [
                "result" => "SUCCESS",
                "session_token" => $sessionStr,
                "user_id" => $identity->getUserId(),
                "account_id" => $identity->getAccountId(),
            ];
        }

        $response->write($ret);
        return $response;
    }

    /**
     * Authenticate a new user - POST version
     */
    public function postAuthenticateAction(HttpRequest $request): HttpResponse
    {
        return $this->getAuthenticateAction($request);
    }

    /**
     * Clear an identity and log out
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getLogoutAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        // Destroy any cookies
        $request->setParam("Authentication", null);
        if (!Console::isConsole()) {
            unset($_COOKIE['Authentication']);
            setcookie('Authentication', null, -1, '/');
        }

        $response->write(["result" => "SUCCESS"]);
        return $response;
    }

    /**
     * POST pass-through for logout
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postLogoutAction(HttpRequest $request): HttpResponse
    {
        return $this->getLogoutAction($request);
    }

    /**
     * Check if a session is still valid
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getCheckinAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);
        $ret = ["result" => ($this->authService->getIdentity()) ? "OK" : "FAIL"];

        if (!Console::isConsole() && $ret['OK'] && !isset($_COOKIE['Authentication'])) {
            // Set the cookie for future requests to the server
            setcookie(
                'Authentication',
                $request->getParam('Authentication'),
                $this->authService->getExpiresTs(),
                '/'
            );
        } elseif (isset($_COOKIE['Authentication'])) {
            // Clear all cookies if check fails
            unset($_COOKIE['Authentication']);
            setcookie('Authentication', null, -1, '/');
        }

        $response->write($ret);
        return $response;
    }

    /**
     * POST pass-through for checkin
     *
     * @return array|string
     */
    public function postCheckinAction(HttpRequest $request): HttpResponse
    {
        return $this->getCheckinAction($request);
    }

    /**
     * Get all accounts associated with a domain and return the name and instance URL
     */
    public function postGetAccountsAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);
        $email = $request->getParam("email");

        // Check if the request was sent as a json object
        if ($request->getParam('Content-Type') === 'application/json') {
            $body = json_decode($request->getBody(), true);
            $email = $body['email'];
        }

        // TODO: Figure out a way to authorize the requestor so that
        // a bot cannot use this endpoint to validate email addresses.

        $ret = [];

        if ($email) {
            $ret = $this->application->getAccountsByEmail($email);
        }

        $response->write($ret);
        return $response;
    }

    /**
     * Get all accounts associated with a domain and return the name and instance URL
     */
    public function getGetAccountsAction(HttpRequest $request): HttpResponse
    {
        return $this->postGetAccountsAction($request);
    }
}
