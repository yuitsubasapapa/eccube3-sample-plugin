<?php

namespace Plugin\CategoryContent;

use Eccube\Event\RenderEvent;
use Eccube\Event\ShoppingEvent;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Controller\Util\CommonUtil;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Util;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Member;
use Plugin\GmoPaymentGateway\Controller\Helper\PageHelper_PaymentEdit;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\CssSelector\CssSelector;

class CategoryContent
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onRenderAdminProductCategoryEditBefore(FilterResponseEvent $event)
    {
        $app = $this->app;
        $request = $event->getRequest();
        $response = $event->getResponse();
        $id = $request->attributes->get('id');

        // DomCrawlerにHTMLを食わせる
        $html = $response->getContent();
        $crawler = new Crawler($html);

        $CategoryContent = $app['category_content.repository.category_content']->find($id);
        if (is_null($CategoryContent)) {
            $CategoryContent = new \Plugin\CategoryContent\Entity\CategoryContent();
        }

        $form = $app['form.factory']
            ->createBuilder('admin_category')
            ->getForm();
        $form->get('content')->setData($CategoryContent->getContent());
        $form->handleRequest($request);

        $twig = $app->renderView(
            'CategoryContent/Resource/template/Admin/category.twig',
            array('form' => $form->createView())
        );

        $oldCrawler = $crawler
            ->filter('form')
            ->first();

        // DomCrawlerからHTMLを吐き出す
        $html = $crawler->html();
        $oldHtml = '';
        $newHtml = '';
        if (count($oldCrawler) > 0) {
            $oldHtml = $oldCrawler->html();
            $newHtml = $oldHtml . $twig;
        }

        $html = str_replace($oldHtml, $newHtml, $html);

        $response->setContent($html);
        $event->setResponse($response);
    }

    public function onAdminProductCategoryEditAfter()
    {
        $app = $this->app;
        $id = $app['request']->attributes->get('id');

        $form = $app['form.factory']
            ->createBuilder('admin_category')
            ->getForm();

        $CategoryContent = $app['category_content.repository.category_content']->find($id);
        if (is_null($CategoryContent)) {
            $CategoryContent = new \Plugin\CategoryContent\Entity\CategoryContent();
        }
        $form->get('content')->setData($CategoryContent->getContent());

        $form->handleRequest($app['request']);

        if ('POST' === $app['request']->getMethod()) {
            if ($form->isValid()) {
                $content = $form->get('content')->getData();

                $Category = $app['eccube.repository.category']->find($id);

                $CategoryContent
                    ->setCategoryId($Category->getId())
                    ->setCategory($Category)
                    ->setContent($content);

                $app['orm.em']->persist($CategoryContent);
                $app['orm.em']->flush();
            }
        }
    }

}