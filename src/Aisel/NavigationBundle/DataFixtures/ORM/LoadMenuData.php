<?php

/*
 * This file is part of the Aisel package.
 *
 * (c) Ivan Proskuryakov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aisel\NavigationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Aisel\NavigationBundle\Entity\Menu;

/**
 * Navigation menu fixtures
 *
 * @author Ivan Proskoryakov <volgodark@gmail.com>
 */
class LoadMenuData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // referenced Page
        $pinned = $this->getReference('about-page');

        // Blog
        $menu = new Menu();
        $menu->setTitle('Blog');
        $menu->setMetaUrl('/#!/pages/');
        $menu->setStatus(true);
        $menu->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $menu->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $manager->persist($menu);
        $manager->flush();

        // Categories
        $menu = new Menu();
        $menu->setTitle('Categories');
        $menu->setMetaUrl('/#!/categories/');
        $menu->setStatus(true);
        $menu->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $menu->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $manager->persist($menu);
        $manager->flush();

        // Contact
        $menu = new Menu();
        $menu->setTitle('Contact');
        $menu->setMetaUrl('/#!/contact/');
        $menu->setStatus(true);
        $menu->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $menu->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $manager->persist($menu);
        $manager->flush();

        // Pinned
        $menu = new Menu();
        $menu->setTitle('About');
        $menu->setMetaUrl('/#!/page/' . $pinned->getMetaUrl());
        $menu->setStatus(true);
        $menu->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $menu->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $manager->persist($menu);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 900;
    }
}
