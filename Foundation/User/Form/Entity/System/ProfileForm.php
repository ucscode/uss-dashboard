<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\System;

use Module\Dashboard\Bundle\FileUploader\FileUploader;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;

class ProfileForm extends AbstractUserAccountForm
{
    public const AVATAR_COLLECTION = 'avatar';
    public const FILE_TYPES = ['jpg', 'png', 'gif', 'jpeg', 'webp'];
    public readonly Collection $avatarCollection;
    protected ?User $user;

    protected function buildForm(): void
    {
        $this->attribute->setEnctype('multipart/form-data');
        $this->user = (new User())->acquireFromSession();
        $this->createLocalCollectionFields();
        $this->avatarCollection = new Collection();
        $this->addCollection(self::AVATAR_COLLECTION, $this->avatarCollection);
        $this->createAvatarField();
        $this->populateFields();
    }

    protected function validateResource(array $filteredResource): ?array
    {
        // use try catch block;
        $file = $this->getAvatarMetaValue();
        return [];
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {

    }

    protected function createLocalCollectionFields(): void
    {
        $this->createEmailField();

        $textareaField = $this->createCustomField([
            'nodeName' => Field::NODE_TEXTAREA,
            'name' => 'meta[biography]',
            'placeholder' => 'Enter your bio',
            'label' => 'Biography'
        ]);

        $textareaField->getElementContext()
            ->widget
                ->setAttribute('rows', 5)
                ->removeAttribute('required');
        ;

        $this->createNonceField();
        $this->createSubmitButton();
    }

    protected function createAvatarField(): void
    {
        $avatarField = new Field(Field::NODE_INPUT, Field::TYPE_FILE);
        $elementContext = $avatarField->getElementContext();
        $elementContext->widget
            ->setAttribute('accept', implode(', ', self::FILE_TYPES))
            ->setAttribute('data-ui-preview-uploaded-image-in', '#image')
            ->setAttribute('id', 'input')
            ->removeAttribute('required')
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

        $this->avatarCollection->addField('meta[avatar]', $avatarField);
        $this->avatarCollection->addField('void', $buttonField);
    }

    protected function getAvatarMetaValue(): ?string
    {
        $file = [];

        foreach($_FILES['meta'] as $key => $list) {
            $file[$key] = $list['avatar'];
        }

        $uploader = (new FileUploader($file))
            ->setMimeTypes([
                'image/png',
                'image/jpeg',
                'image,jpg',
                'image/webp',
            ])
            ->setUploadDirectory('')
            ->setFilenamePrefix($this->user->getId() . "-")
            ->setMaxFileSize(1000 * 1024) // 1000 KB
        ;
        var_dump($uploader);

        if(!$uploader->uploadFile()) {
            throw new \Exception($uploader->getError(true));
        }

        return $uploader->getUploadedFilepath();
    }

    protected function populateFields(): void
    {
        $this->populate([
            'user' => $this->user->getRawInfo()
        ]);
    }
}
