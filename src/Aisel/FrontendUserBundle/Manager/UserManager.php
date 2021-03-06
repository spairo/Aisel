<?php

/*
 * This file is part of the Aisel package.
 *
 * (c) Ivan Proskuryakov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aisel\FrontendUserBundle\Manager;

use Aisel\FrontendUserBundle\Entity\FrontendUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Aisel\ResourceBundle\Utility\PasswordUtility;

/**
 * Manager for frontend users. Register, Load and others ...
 *
 * @author Ivan Proskoryakov <volgodark@gmail.com>
 */
class UserManager implements UserProviderInterface
{
    protected $encoder;
    protected $em;
    protected $templating;
    protected $mailer;
    protected $websiteEmail;
    protected $securityContext;

    public function __construct($em, $encoder, $mailer, $templating, $websiteEmail, $securityContext)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->encoder = $encoder;
        $this->em = $em;
        $this->websiteEmail = $websiteEmail;
        $this->securityContext = $securityContext;
    }

    /**
     * Get Templating service
     */
    public function getTemplating()
    {
        return $this->templating;
    }

    /**
     * Get Mailer service
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    protected function getRepository()
    {
        return $this->em->getRepository('AiselFrontendUserBundle:FrontendUser');
    }

    public function checkUserPassword(FrontendUser $user, $password)
    {
        $encoder = $this->encoder->getEncoder($user);

        if (!$encoder) {
            return false;
        }
        $isValid = $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());

        return $isValid;
    }

    /**
     * Create User, specially for fixtures
     */
    public function registerFixturesUser(Array $userData)
    {
        $user = new FrontendUser();
        $encoder = $this->encoder->getEncoder($user);
        $encodedPassword = $encoder->encodePassword($userData['password'], $user->getSalt());

        $user->setEmail($userData['email']);
        $user->setUsername($userData['username']);
        $user->setPassword($encodedPassword);
        $user->setEnabled($userData['enabled']);
        $user->setLocked($userData['locked']);

        $user->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $user->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $user->setLastLogin(new \DateTime(date('Y-m-d H:i:s')));

        $user->setPhone($userData['phone']);
        $user->setWebsite($userData['website']);
        $user->setFacebook($userData['facebook']);
        $user->setTwitter($userData['twitter']);
        $user->setLinkedin($userData['linkedin']);
        $user->setGoogleplus($userData['googleplus']);
        $user->setGithub($userData['github']);
        $user->setBehance($userData['behance']);
        $user->setAbout($userData['about']);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Update User details
     * @param  array  $userData
     * @return string $message
     */
    public function updateDetailsCurrentUser(Array $userData)
    {
        try {
            $user = $this->securityContext->getToken()->getUser();

            if ($userData['phone']) $user->setPhone($userData['phone']);
            if ($userData['website']) $user->setWebsite($userData['website']);
            if ($userData['about']) $user->setAbout($userData['about']);

            if ($userData['facebook']) $user->setFacebook($userData['facebook']);
            if ($userData['twitter']) $user->setTwitter($userData['twitter']);
            if ($userData['linkedin']) $user->setLinkedin($userData['linkedin']);
            if ($userData['googleplus']) $user->setGoogleplus($userData['googleplus']);
            if ($userData['github']) $user->setGithub($userData['github']);
            if ($userData['behance']) $user->setBehance($userData['behance']);

            $this->em->persist($user);
            $this->em->flush();
            $message = 'Information successfully updated!';
        } catch (\Swift_TransportException $e) {
            $message = $e->getMessage();
        }

        return $message;
    }

    /**
     *   Register user and send userinfo by email
     */
    public function registerUser(Array $userData)
    {
        $user = $this->loadUserByUsername($userData['username']);

        if (!$user) {
            $user = new FrontendUser();
            $encoder = $this->encoder->getEncoder($user);
            $encodedPassword = $encoder->encodePassword($userData['password'], $user->getSalt());

            $user->setEmail($userData['email']);
            $user->setUsername($userData['username']);
            $user->setPassword($encodedPassword);
            $user->setEnabled(true);
            $user->setLocked(false);

            $user->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
            $user->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
            $user->setLastLogin(new \DateTime(date('Y-m-d H:i:s')));

            $this->em->persist($user);
            $this->em->flush();

            // Send user info via email
            try {
                $message = \Swift_Message::newInstance()
                    ->setSubject('New Account!')
                    ->setFrom($this->websiteEmail)
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->getTemplating()->render(
                            'AiselFrontendUserBundle:Email:registration.txt.twig',
                            array(
                                'username' => $user->getUsername(),
                                'password' => $userData['password'],
                                'email' => $user->getEmail(),
                            )
                        )
                    );

                $this->getMailer()->send($message);
            } catch (\Swift_TransportException $e) {
            }

            return $user;

        } else {
            return false;
        }

    }

    /**
     *   Reset and send password by email
     */
    public function resetPassword($user)
    {
        if ($user) {
            $utility = new PasswordUtility();
            $password = $utility->generatePassword();

            $encoder = $this->encoder->getEncoder($user);
            $encodedPassword = $encoder->encodePassword($password, $user->getSalt());

            $user->setPassword($encodedPassword);

            // Send password via email
            try {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Password reset')
                    ->setFrom($this->websiteEmail)
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->getTemplating()->render(
                            'AiselFrontendUserBundle:Email:newPassword.txt.twig',
                            array(
                                'username' => $user->getUsername(),
                                'password' => $password,
                            )
                        )
                    );
                $response = $this->getMailer()->send($message);
            } catch (\Swift_TransportException $e) {
                $response = $e->getMessage();
            }

            $this->em->persist($user);
            $this->em->flush();

            return $response;

        } else {
            return false;
        }
    }

    public function loadUserByUsername($username)
    {
        $user = $this->getRepository()->findOneBy(array('username' => $username));

        return $user;
    }

    public function findUserByEmail($email)
    {
        $user = $this->getRepository()->findOneBy(array('email' => $email));

        return $user;
    }

    public function findUser($username, $email)
    {
        $user = $this->em->getRepository('AiselFrontendUserBundle:FrontendUser')->findUser($username, $email);

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
        || is_subclass_of($class, $this->getEntityName());
    }

}
