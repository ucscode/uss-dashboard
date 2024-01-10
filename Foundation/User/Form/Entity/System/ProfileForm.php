<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\System;

use Exception;
use Module\Dashboard\Bundle\FileUploader\FileUploader;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Module\Dashboard\Foundation\User\Form\Service\EmailResolver;
use Module\Dashboard\Foundation\User\Form\Service\Validator;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;

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
        $uss = Uss::instance();

        if($resource = $this->validateNonce($filteredResource)) {

            $resource['user']['email'] = $email = trim(strtolower($resource['user']['email']));
            $resource['meta'] ??= [];

            $validator = new Validator();
            $isValidEmail = $validator->validateEmail($this->collection, $email);

            if($isValidEmail) {

                try {

                    $file = $this->getUploadedAvatarValue();
                    if($file !== null) {
                        $resource['meta']['avatar'] = $file;
                    };

                    if($uss->options->get('user:reconfirm-email') && $email != $this->user->getEmail()) {
                        $alternateUser = (new User())->allocate("email", $email);
                        if($alternateUser->isAvailable()) {
                            throw new Exception("The updated email address is already allocated to another account");
                        }
                    }

                    return $resource;

                } catch(Exception $e) {

                    $modal = new Modal();
                    $modal->setTitle("Profile Update Error");
                    $modal->setMessage($e->getMessage());

                    Flash::instance()->addModal("profile-error", $modal);

                }

            } // valid email

        }; // resource

        return null;
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        if($validatedResource) {

            $uss = Uss::instance();

            $modal = new Modal();
            $title = "Profile Update Failed";

            $emailConfirmationRequired = $uss->options->get('user:reconfirm-email');
            $updatedEmail = $validatedResource['user']['email'];

            $message = "Your profile update could not be completed!";
            $summary = '';

            if(!$emailConfirmationRequired) {
                $this->user->setEmail($updatedEmail);
            };

            if($this->user->persist()) 
            {
                $title = "Profile Update Successful";
                $message = "Congratulations! Your profile has been successfully updated";

                if($emailConfirmationRequired) 
                {
                    $mailContext = $this->sendReconfirmationEmail($updatedEmail);
                    $summary = sprintf(
                        "<div class='my-2 small alert alert-%s'>%s</div>",
                        $mailContext['status'] ? 'success' : 'secondary',
                        $mailContext['summary']
                    );
                }
            }

            $message .= $summary;
            $modal->setTitle($title);
            $modal->setMessage($message);

            Flash::instance()->addModal("user-profile", $modal);

            return $this->user;

        }

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

    protected function getUploadedAvatarValue(): ?string
    {
        $file = [];

        foreach($_FILES['meta'] as $key => $list) {
            $file[$key] = $list['avatar'];
        }

        $uploader = (new FileUploader($file))
            ->setMimeTypes([
                'image/png',
                'image/jpeg',
                'image/jpg',
                'image/webp',
            ])
            ->setUploadDirectory(DashboardImmutable::ASSETS_DIR . "/images/profile")
            ->setFilenamePrefix($this->user->getId() . "-")
            ->setMaxFileSize(1000 * 1024) // 1000 KB
        ;

        if(!$uploader->uploadFile()) {
            if($uploader->getError() !== 4) {
                throw new Exception($uploader->getError(true));
            }
        }

        return $uploader->getUploadedFilepath();
    }

    protected function populateFields(): void
    {
        $this->populate([
            'user' => $this->user->getRawInfo()
        ]);
    }

    protected function sendReconfirmationEmail(string $email): array
    {
        $mailer = [];
        $emailResolver = new EmailResolver($this->getProperties());
        $mailer['status'] = $emailResolver->sendProfileUpdateEmail($this->user, $email);
        $mailer['summary'] = $mailer['status'] ?
            "Please confirm the link sent to your new email" :
            "Unable to send a confirmation link to your new email address";
        return $mailer;
    }
}
