<?php

namespace App\EntityListener;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function prePersist( User $user)
    {
        $this->encodePassword($user);
    }
    public function preUpdate( User $user)
    {
        $this->encodePassword($user);
    }
    public function encodePassword( User $user)
    {
        if($user->getPlainPassword() === null) {
            return ;
        }
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPlainPassword()));
        // remove plain password to avoid saving it in the database.
        $user->setPlainPassword(null);
    }
}