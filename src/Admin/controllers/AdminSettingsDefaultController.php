<?php

class AdminSettingsDefaultController implements RouteInterface
{
    public function __construct(
        protected PageManager $pageManager,
        protected DashboardInterface $dashboardInterface
    ){}
    
    public function onload(array $matches)
    {
        
    }
}