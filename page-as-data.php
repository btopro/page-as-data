<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

class pageAsDataPlugin extends Plugin
{
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized()
    {
        $accept = $_SERVER['HTTP_ACCEPT'];

        if (strlen($accept) > 0 && $accept == 'application/json') {
            $this->enable([
                    'onPageInitialized' => ['onPageInitialized', 0]
                ]);
        }
    }

    public function onPageInitialized()
    {
        /**
         * @var \Grav\Common\Page\Page $page
         */
        $page = $this->grav['page'];
        $collection = $page->collection('content', false);
        $pageArray = $page->toArray();
        $children = array();
        foreach ($collection as $item) {
            $children[] = $item->toArray();
        }
        $pageArray['children'] = $children;

    }
}