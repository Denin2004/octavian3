<?php
namespace App\Services\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class WebserviceUser implements UserInterface, EquatableInterface
{
    private $username;
    private $password;
    private $salt = '';
    private $roles = ['ROLE_ADMIN'];
    private $login;
    private $userId;
    private $iCompId;
    private $userAccess = [];

    public function __construct($username, $id, $compId, $access)
    {
        $this->username = $username;
        $this->login = $username;
        $this->userId = $id;
        $this->iCompId = $compId;
        $this->userAccess = $access;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }
        return true;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getIRoleId()
    {
        return $this->iRoleId;
    }

    public function getICompId()
    {
        return $this->iCompId;
    }

    public function loginName()
    {
        return $this->login;
    }

    public function getAccess($subject)
    {
        $route = $subject->attributes->get('_route');
        if (in_array($route, ['reportPage', 'reportData', 'reportMetaData'])) {
            return !(array_search($subject->attributes->get('id'), $this->userAccess['reports']) === false);
        }
        return !(array_search($route, $this->userAccess['routes']) === false);
    }

    public function getReportAccess($id)
    {
        return !(array_search($id, $this->userAccess['reports']) === false);
    }

    public function getRouteAccess($route)
    {
        return !(array_search($route, $this->userAccess['routes']) === false);
    }

    public function getWidgetsAccess()
    {
        return $this->userAccess['widgets'];
    }

    public function setPassword($psw)
    {
        $this->password = $psw;
    }
}
