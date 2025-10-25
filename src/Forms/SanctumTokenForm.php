<?php

namespace Tiryaq\Api\Forms;

use Tiryaq\Api\Http\Requests\StoreSanctumTokenRequest;
use Tiryaq\Api\Models\PersonalAccessToken;
use Tiryaq\Base\Forms\FieldOptions\NameFieldOption;
use Tiryaq\Base\Forms\Fields\TextField;
use Tiryaq\Base\Forms\FormAbstract;

class SanctumTokenForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new PersonalAccessToken())
            ->setValidatorClass(StoreSanctumTokenRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->toArray());
    }
}
