<?php

abstract class AbstractPageFactory
{
    public function __construct(protected DashboardInterface $dashboard) {}

    /**
    * @method createPage
    */
   protected function createPage(string $name, bool $route = true): PageManager
   {
       $pageManager = new PageManager($name);
       $pageManager->setRoute($route ? "/{$name}" : null);
       $this->dashboard->pageRepository->addPageManager($name, $pageManager);
       return $pageManager;
   }
}