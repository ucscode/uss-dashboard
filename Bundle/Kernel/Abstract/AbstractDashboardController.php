<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Uss\Component\Route\RouteInterface;

class AbstractDashboardController implements RouteInterface
{
    /**
     * Composes the application by handling the $form and updating the $dashboard or $document components.
     * Example of actions to execute within this method:
     * 
     * - $document->setTemplate("your updated template")
     * - $document->setContext(["value" => "Your custom context"])
     * - $dashboard->yourUniqueAction()
     * - $form->handleSubmission() etc
     * 
     * The global dashboard controller will internally handle the remaining processes.
     * 
     * If you intend to enforce your own render logic, you can use the `$dashboard->render()` method within this method!
     *
     * @param DashboardInterface          $dashboard      The dashboard component to be updated.
     * @param Document                    $document       The document component to be updated.
     * @param DashboardFormInterface|null $form           The optional dashboard form to handle submissions.
     *
     * @return void
     */
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        // Your code here
    }

    public function onload(array $context): void
    {
        $this->GUIBuilder(
            $context['dashboardInterface'], 
            $context['dashboardDocument'],
            $context['dashboardDocument']?->getCustom('app.form')
        );
    }

    protected function GUIBuilder(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $document->setContext($document->getContext() + ['form' => $form]);
        $this->composeApplication($dashboard, $document, $form);
    }
}
