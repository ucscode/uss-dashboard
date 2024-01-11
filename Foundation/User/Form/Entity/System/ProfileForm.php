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
        (new EmailResolver([]))->verifyProfileUpdateEmail();
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
                        $resource['meta']['avatar'] = $uss->pathToUrl($file);
                    };

                    if($uss->options->get('user:reconfirm-email') && $email != $this->user->getEmail()) {
                        $alternateUser = (new User())->allocate("email", $email);
                        if($alternateUser->isAvailable()) {
                            throw new Exception("The updated email address is already allocated to another account");
                        }
                    }

                    if(!empty($resource['discard-pending-email'])) {
                        $this->user->meta->remove('profile-email:code');
                        unset($resource['discard-pending-email']);
                    }

                    return $resource;

                } catch(Exception $e) {

                    $modal = new Modal();
                    $modal->setTitle("Profile Update Error");
                    $modal->setMessage(str_replace("\n", "<br/>", $e->getMessage()));

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
            $validatedResource = $uss->sanitize($validatedResource);

            $modal = new Modal();
            $title = "Profile Update Failed";

            $currentEmail = $this->user->getEmail();
            $updatedEmail = $validatedResource['user']['email'];
            $confirmationRequired = $uss->options->get('user:reconfirm-email');

            $message = "Your profile update could not be completed!";
            $summary = '';

            $this->user->setEmail($confirmationRequired ? $currentEmail : $updatedEmail);

            $persisted = $this->user->persist();

            if($persisted) {

                $title = "Profile Update Successful";
                $message = "Congratulations! Your profile has been successfully updated";

                foreach($validatedResource['meta'] as $key => $value) {
                    $this->user->meta->set("user.{$key}", $value);
                };

                if($confirmationRequired && $currentEmail !== $updatedEmail) {
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

            return [
                'resource' => $validatedResource,
                'persisted' => $persisted,
                'user' => $this->user
            ];

        }

        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        $this->populateFields();
    }

    protected function createLocalCollectionFields(): void
    {
        $uss = Uss::instance();
        $emailContext = $this->createEmailField()->getElementContext();
        $emailContext->widget
            ->setReadonly((bool)$uss->options->get('user:readonly-email'))
        ;

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
        $this->createSubmitButton('Save Changes');
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
        $metas = $this->user->meta->getAll('user.%');
        $keys = array_map(fn ($key) => str_replace("user.", '', $key), array_keys($metas));
        $values = array_column($metas, "value");
        $metaOutcome = array_combine($keys, $values);

        $this->populate([
            'user' => $this->user->getRawInfo(),
            'meta' => $metaOutcome
        ]);

        $emailContext = $this->collection->getField('user[email]')?->getElementContext();
        $emailContext?->info->setValue($this->generateEmailFieldInfo());
    }

    protected function sendReconfirmationEmail(string $email): array
    {
        $mailer = [];
        $emailResolver = new EmailResolver($this->getProperties());
        $mailer['status'] = $emailResolver->sendProfileUpdateEmail($this->user, $email);
        $mailer['summary'] = $mailer['status'] ?
            "Please confirm the link sent to your new email" :
            "<i class='bi bi-exclamation-circle'></i> Email not updated: <br/> Unable to send a confirmation link to the new email address";
        return $mailer;
    }

    protected function generateEmailFieldInfo(): ?string
    {
        $profileContext = $this->user->meta->get('profile-email:code');

        $discardField = new Field(Field::NODE_INPUT, Field::TYPE_CHECKBOX);
        $discardContext = $discardField->getElementContext();

        $discardContext->label
            ->setValue("Discard email confirmation")
            ->setAttribute('for', 'discard-pending-email')
        ;

        $discardContext->container
            ->removeClass('my-1')
            ->addClass('mb-0')
        ;

        $discardContext->widget
            ->setRequired(false)
            ->setAttribute('id', 'discard-pending-email')
            ->setAttribute('name', 'discard-pending-email')
            ->setValue(1)
        ;

        $template = "<div class='small overflow-auto border rounded-1 py-2 px-3'>
            <div class='mb-1 border-bottom'>
                <i class='bi bi-info-circle me-1'></i> 
                <span class='text-danger'>Unconfirmed pending email</span>
                <span class='text-nowrap'>( <span class='text-info'>%s</span> )</span>
            </div>
            <div class='discard-gadget'>%s</div>
        </div>";

        if($profileContext) {
            $pendingEmail = $profileContext['data']['email'];
            return sprintf($template, $pendingEmail, $discardContext->gadget->export());
        }

        return null;
    }
}
