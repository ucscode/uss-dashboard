<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Uss\Component\Route\RouteInterface;

class AbstractDashboardController implements RouteInterface
{
    protected DashboardInterface $dashboard;
    protected Document $document;
    protected ?DashboardFormInterface $form;

    /**
     * To use this, call the `parent::onload($context)`, then make your update to the references. Example:
     * 
     * - $this->document->setTemplate("your updated template")
     * - $this->document->setContext(["value" => "Your custom context"])
     * - $this->form->handleSubmission() etc
     * 
     * The global dashboard controller will internally handle the remaining processes.
     * If you intend to enforce your own render logic, you can use the `$this->dashboard->render()` method!
     *
     * @param DashboardInterface          $dashboard      The dashboard component to be updated.
     * @param Document                    $document       The document component to be updated.
     * @param DashboardFormInterface|null $form           The optional dashboard form to handle submissions.
     *
     * @return void
     */
    public function onload(array $context): void
    {
        $this->dashboard = $context['dashboard'];
        $this->document = $context['document'];
        $this->form = $this->document?->getCustom('app.form');
        $this->document->setContext($this->document->getContext() + [
            'form' => $this->form
        ]);
    }
}
