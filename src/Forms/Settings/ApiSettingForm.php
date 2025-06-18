<?php

namespace Botble\Api\Forms\Settings;

use Botble\Api\Facades\ApiHelper;
use Botble\Api\Http\Requests\ApiSettingRequest;
use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;

class ApiSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(ApiSettingRequest::class)
            ->setSectionTitle(trans('packages/api::api.setting_title'))
            ->setSectionDescription(trans('packages/api::api.setting_description'))
            ->contentOnly()
            ->addGeneralSettings()
            ->addSecuritySettings()
            ->addHelpSection();
    }

    protected function addGeneralSettings(): static
    {
        return $this
            ->addOpenFieldset('general', ['class' => 'form-fieldset'])
            ->add(
                'general_section_title',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h5 class="mb-3">' . trans('packages/api::api.api_general_section') . '</h5>')
            )
            ->add(
                'api_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('packages/api::api.api_enabled'))
                    ->helperText(trans('packages/api::api.api_enabled_description'))
                    ->value(ApiHelper::enabled())
                    ->toArray()
            )
            ->addCloseFieldset('general');
    }

    protected function addSecuritySettings(): static
    {
        $apiKey = ApiHelper::getApiKey();
        $hasApiKey = !empty($apiKey);

        return $this
            ->addOpenFieldset('security', ['class' => 'form-fieldset mt-4'])
            ->add(
                'security_section_title',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h5 class="mb-3">' . trans('packages/api::api.api_security_section') . '</h5>')
            )
            ->when($hasApiKey, function ($form) {
                return $form->add(
                    'api_key_status',
                    AlertField::class,
                    AlertFieldOption::make()
                        ->type('success')
                        ->content('<strong>API Key Protection:</strong> Enabled. All API requests require the X-API-KEY header.')
                );
            })
            ->when(!$hasApiKey, function ($form) {
                return $form->add(
                    'api_key_status',
                    AlertField::class,
                    AlertFieldOption::make()
                        ->type('warning')
                        ->content('<strong>API Key Protection:</strong> Disabled. API endpoints are publicly accessible.')
                );
            })
            ->add(
                'api_key_wrapper',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content($this->getApiKeyFieldWithActions($apiKey))
            )
            ->addCloseFieldset('security');
    }

    protected function addHelpSection(): static
    {
        return $this
            ->addOpenFieldset('help', ['class' => 'form-fieldset mt-4'])
            ->add(
                'help_section_title',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h5 class="mb-3">' . trans('packages/api::api.api_help_section') . '</h5>')
            )
            ->add(
                'api_documentation_info',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content($this->getDocumentationSection())
            )
            ->add(
                'api_usage_examples',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content($this->getUsageExamples())
            )
            ->addCloseFieldset('help');
    }

    protected function getApiKeyActions(): string
    {
        return view('packages/api::settings.partials.api-key-actions')->render();
    }

    protected function getApiKeyFieldWithActions(string $apiKey): string
    {
        return view('packages/api::settings.partials.api-key-field', compact('apiKey'))->render();
    }

    protected function getDocumentationSection(): string
    {
        return view('packages/api::settings.partials.documentation-section')->render();
    }

    protected function getUsageExamples(): string
    {
        return view('packages/api::settings.partials.usage-examples')->render();
    }
}
