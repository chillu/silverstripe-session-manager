<?php

namespace Kinglozzer\SessionManager\Security;

use Kinglozzer\SessionManager\Model\LoginSession;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\AuthenticationHandler;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;

/**
 * This is separate to LogOutAuthenticationHandler so that it can be registered with
 * Injector and called *after* the other AuthenticationHandler::logIn() implementations
 */
class LogInAuthenticationHandler implements AuthenticationHandler
{
    /**
     * @var string
     */
    protected $sessionVariable;

    /**
     * @var RememberLoginHash
     */
    protected $rememberLoginHash;

    /**
     * @return string
     */
    public function getSessionVariable()
    {
        return $this->sessionVariable;
    }

    /**
     * @param string $sessionVariable
     */
    public function setSessionVariable($sessionVariable)
    {
        $this->sessionVariable = $sessionVariable;
    }

    /**
     * @return string
     */
    public function getRememberLoginHash()
    {
        return $this->rememberLoginHash;
    }

    /**
     * @param RememberLoginHash $rememberLoginHash
     */
    public function setRememberLoginHash(RememberLoginHash $rememberLoginHash)
    {
        $this->rememberLoginHash = $rememberLoginHash;
    }

    public function authenticateRequest(HTTPRequest $request)
    {
    }

    public function logIn(Member $member, $persistent = false, HTTPRequest $request = null)
    {
        $loginSession = LoginSession::find($member, $request);
        if (!$loginSession) {
            $loginSession = LoginSession::generate($member, $persistent, $request);
        }

        $loginSession->LastAccessed = DBDatetime::now()->Rfc2822();
        $loginSession->IPAddress = $request->getIP();
        $loginSession->write();

        if ($persistent && $rememberLoginHash = $this->getRememberLoginHash()) {
            $rememberLoginHash->LoginSessionID = $loginSession->ID;
            $rememberLoginHash->write();
        }

        $request->getSession()->set($this->getSessionVariable(), $loginSession->ID);
    }

    public function logOut(HTTPRequest $request = null)
    {
    }
}
