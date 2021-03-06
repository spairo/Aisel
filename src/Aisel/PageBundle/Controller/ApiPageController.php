<?php

/*
 * This file is part of the Aisel package.
 *
 * (c) Ivan Proskuryakov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aisel\PageBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Frontend Page REST API controller
 *
 * @author Ivan Proskoryakov <volgodark@gmail.com>
 */
class ApiPageController extends Controller
{

    private function isAuthenticated()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if ($user !== 'anon.') {
            if (in_array('ROLE_USER', $user->getRoles())) return true;
        }

        return false;
    }

    /**
     * @Rest\View
     * /api/page/list.json?limit=2&current=3
     */
    public function pageListAction(Request $request)
    {

        $params = array(
            'current' => $request->get('current'),
            'limit' => $request->get('limit'),
            'category' => $request->get('category')
        );

        if ($request->get('user') && $this->isAuthenticated()) {
            $userid = $this->get('security.context')->getToken()->getUser()->getId();
            $params['userid'] = $userid;
        }

        $pageList = $this->container->get("aisel.page.manager")->getPages($params);

        return $pageList;

    }

    /**
     * @Rest\View
     */
    public function pageViewByURLAction($urlKey)
    {
        /** @var \Aisel\PageBundle\Entity\Page $page */
        $page = $this->container->get("aisel.page.manager")->getPageByURL($urlKey);

        return $page;
    }
}
