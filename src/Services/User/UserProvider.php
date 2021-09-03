<?php
namespace App\Services\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Services\User\WebserviceUser;
use App\Services\MyACP\MyACP;
use App\Services\Security\Access;
use App\Entity\Users;

class UserProvider implements UserProviderInterface
{
    protected $requestStack;
    protected $myACP;
    protected $encoder;
    protected $access;
    protected $usersDB;

    public function __construct(RequestStack $requestStack, MyACP $myACP, UserPasswordEncoderInterface $encoder, Access $access, Users $usersDB)
    {
        $this->requestStack = $requestStack;
        $this->myACP = $myACP;
        $this->encoder = $encoder;
        $this->access = $access;
        $this->usersDB = $usersDB;
    }

    public function loadUserByUsername($username)
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($username == '') {
            throw new BadCredentialsException('login.errors.user_name_blank');
        }
        $password = $request->get('_password');
        if ($password == '') {
            throw new BadCredentialsException('login.errors.password_blank');
        }
        $auth = $this->myACP->tryConnect($username, $password);
        switch ($auth) {
            case 'Ok':
                $userId = $this->usersDB->getUserId($username);
                if ($userId < 0) {
                    throw new BadCredentialsException('login.errors.no_such_user');
                }
                $user =  new WebserviceUser(
                    $username,
                    $userId,
                    1,
                    $this->access->user($userId)
                );
                $user->setPassword($this->encoder->encodePassword($user, $password));

                if ($this->encoder->isPasswordValid($user, $password)) {
                    return $user;
                }
                throw new BadCredentialsException($auth);
            default:
                throw new BadCredentialsException($auth);
        }
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }
        $this->myACP->setUser($user);
        return $user;
    }

    public function supportsClass($class)
    {
        return WebserviceUser::class === $class;
    }
}
