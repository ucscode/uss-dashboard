<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\System;

use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;

class ProfileForm extends AbstractUserAccountForm
{
    public const AVATAR_COLLECTION = 'avatar';
    public const FILE_TYPES = ['jpg', 'png', 'gif', 'jpeg', 'webp'];
    public readonly Collection $avatarCollection;

    protected function buildForm(): void
    {
        $this->createEmailField();
        $this->createCustomField([
            'nodeName' => Field::NODE_TEXTAREA,
            'name' => 'meta[biography]',
            'placeholder' => 'Enter your bio',
            'label' => 'spiderman'
        ]);
        $this->createSubmitButton();

        $this->avatarCollection = new Collection();
        $this->addCollection(self::AVATAR_COLLECTION, $this->avatarCollection);
        $this->createAvatarField();
    }

    protected function validateResource(array $filteredResource): ?array
    {
        return [];
    }

    protected function persistResource(?array $validatedResource): mixed
    {

    }

    protected function resolveSubmission(mixed $presistedResource): void
    {

    }

    protected function createAvatarField(): void
    {
        $field = new Field(Field::NODE_INPUT, Field::TYPE_FILE);
        $elementContext = $field->getElementContext();
        $elementContext->widget
            ->setAttribute('accept', implode(', ', self::FILE_TYPES))
            ->setAttribute('data-ui-preview-uploaded-image-in', '#image')
            ->setAttribute('id', 'input')
        ;
        $elementContext->label->setDOMHidden(true);
        $elementContext->info->setDOMHidden(true);
        $elementContext->frame->addClass("d-none");

        $buttonField = new Field(Field::NODE_BUTTON);
        $buttonContext = $buttonField->getElementContext();
        $buttonContext->widget
            ->setButtonContent('Change Photo')
            ->setAttribute('data-ui-transfer-click-event-to', '#input')
        ;

        $this->avatarCollection->addField('meta[avatar]', $field);
        $this->avatarCollection->addField('void', $buttonField);
    }
}
