<?php

/*
 * This file is part of the Aisel package.
 *
 * (c) Ivan Proskuryakov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aisel\PageBundle\Manager;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Aisel\PageBundle\Entity\Page;

/**
 * Manager for Pages, mostly used in REST API
 *
 * @author Ivan Proskoryakov <volgodark@gmail.com>
 */
class UserPageManager
{
    protected $sc;
    protected $em;
    protected $securityContext;
    protected $pageManager;
    protected $user;
    protected $categories = array();

    public function __construct($sc, $em, $securityContext, $pageManager)
    {
        $this->sc = $sc;
        $this->em = $em;
        $this->securityContext = $securityContext;
        $this->pageManager = $pageManager;

        if (!$this->isAuthenticated()) {
            throw new NotFoundHttpException('User not authenticated.');
        } else {
            $this->user = $this->securityContext->getToken()->getUser();
        }
    }

    /**
     * Helper function, get enabled categories from Tree Array
     * @param  array $array
     * @param  array $return
     * @return array $return
     */
    public function flatCategories($array, $return)
    {
        foreach ($array as $k => $v) {
            if (count($v->children)) {
                if ($v->selected) $this->categories[$v->id] = trim($v->title);
                $return = $this->flatCategories($v->children, $return);
            } else {
                if ($v->selected) $this->categories[$v->id] = trim($v->title);
            }
        }

        return $return;
    }

    /**
     * Check is user Authenticated
     * @return boolean
     */
    private function isAuthenticated()
    {
        $user = $this->securityContext->getToken()->getUser();
        if ($user !== 'anon.') {
            if (in_array('ROLE_USER', $user->getRoles())) return true;
        }

        return false;
    }

    /**
     * Load page by Id
     *
     * @param int $pageId
     *
     * @return array $page
     *
     * @throws NotFoundHttpException
     */
    private function loadPage($pageId)
    {
        $page = $this->em->getRepository('AiselPageBundle:Page')->find($pageId);
        if (!($page)) {
            throw new NotFoundHttpException('Nothing found');
        }

        return $page;
    }

    /**
     * Get single detailed page with category by URLKey
     * @param  int   $pageId
     * @return array $page
     */
    public function getPageDetailsById($pageId)
    {
        $page = $this->loadPage($pageId);
        $categories = $this->pageManager->getPageCategories($page);
        $pageDetails = array('page' => $page, 'categories' => $categories);

        return $pageDetails;
    }

    /**
     * Update page by given Id
     * @param  int   $pageId
     * @param  array $details
     * @return array $pageDetails
     */
    public function updatePageId($pageId, $details)
    {
        $jsonDetails = utf8_decode($details);
        $pageDetails = json_decode($jsonDetails);
        $page = $this->loadPage($pageId);
        $url = $page->getMetaUrl();
        $normalUrl = $this->pageManager->normalizePageUrl($url, $pageId);

        if (isset($pageDetails->page->title)) $page->setTitle($pageDetails->page->title);
        if (isset($pageDetails->page->content)) $page->setContent($pageDetails->page->content);
        if (isset($pageDetails->page->status)) $page->setStatus($pageDetails->page->status);
        if (isset($pageDetails->page->meta_title)) $page->setMetaTitle($pageDetails->page->meta_title);
        if (isset($pageDetails->page->meta_url)) $page->setMetaUrl($pageDetails->page->meta_url);
        if (isset($pageDetails->page->meta_keywords)) $page->setMetaKeywords($pageDetails->page->meta_keywords);
        if (isset($pageDetails->page->meta_description)) $page->setMetaKeywords($pageDetails->page->meta_description);
        $page->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $page->setMetaUrl($normalUrl);

        // Remove old and Set new Categories
        if ($cats = $pageDetails->selectedCategories) {
            $this->flatCategories($cats, array());

            $currentCats = $page->getCategories();
            foreach ($currentCats as $c) {
                $page->removeCategory($c);
            }
            foreach ($this->categories as $k => $v) {
                $category = $this->em->getRepository('AiselPageBundle:Category')->find($k);
                $page->addCategory($category);
            }
        }

        $this->em->persist($page);
        $this->em->flush();
        $categories = $this->pageManager->getPageCategories($page);
        $pageDetails = array('page' => $page, 'categories' => $categories);

        return $pageDetails;
    }

    /**
     * Add page from frontend
     * @param  object                        $pageDetails
     * @return \Aisel\PageBundle\Entity\Page $page
     */
    public function addPage($pageDetails)
    {
        $page = new Page();
        if (isset($pageDetails->page->title)) $page->setTitle($pageDetails->page->title);
        if (isset($pageDetails->page->content)) $page->setContent($pageDetails->page->content);
        if (isset($pageDetails->page->status)) $page->setStatus($pageDetails->page->status);
        if (isset($pageDetails->page->meta_title)) $page->setMetaTitle($pageDetails->page->meta_title);
        if (isset($pageDetails->page->meta_url)) $page->setMetaUrl($pageDetails->page->meta_url);
        if (isset($pageDetails->page->meta_keywords)) $page->setMetaKeywords($pageDetails->page->meta_keywords);
        if (isset($pageDetails->page->meta_description)) $page->setMetaKeywords($pageDetails->page->meta_description);
        $page->setHidden(false);
        $page->setCommentStatus(false);
        $page->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $page->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));
        $page->setFrontenduser($this->user);

        // url thing ..
        $url = $page->getMetaUrl();
        if (!$url) $url = $pageDetails->page->title;
        $normalUrl = $this->pageManager->normalizePageUrl($url);
        $page->setMetaUrl($normalUrl);

        // Remove old and Set new Categories
        if ($cats = $pageDetails->selectedCategories) {
            $this->flatCategories($cats, array());

            foreach ($this->categories as $k => $v) {
                $category = $this->em->getRepository('AiselPageBundle:Category')->find($k);
                $page->addCategory($category);
            }
        }

        $this->em->persist($page);
        $this->em->flush();

        return $page;
    }

    /**
     * Add page
     * @param int $pageId
     */
    public function deletePageId($pageId)
    {
        $page = $this->loadPage($pageId);
        $this->em->remove($page);
        $this->em->flush();
    }

}
