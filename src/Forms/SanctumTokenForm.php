<?php

namespace Botble\Api\Forms;

use Botble\Api\Http\Requests\StoreSanctumTokenRequest;
use Botble\Api\Models\PersonalAccessToken;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;

class SanctumTokenForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new PersonalAccessToken())
            ->setValidatorClass(StoreSanctumTokenRequest::class)
            ->add('name', TextField::class, TextFieldOption::make()->label(__('core/base::tables.name'))->toArray());
    }
}
