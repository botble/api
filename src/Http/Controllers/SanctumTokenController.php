<?php

namespace Botble\Api\Http\Controllers;

use Botble\Api\Forms\SanctumTokenForm;
use Botble\Api\Http\Requests\StoreSanctumTokenRequest;
use Botble\Api\Models\PersonalAccessToken;
use Botble\Api\Tables\SanctumTokenTable;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class SanctumTokenController extends BaseController
{
    public function index(SanctumTokenTable $sanctumTokenTable): JsonResponse|View
    {
        PageTitle::setTitle(trans('packages/api::sanctum-token.name'));

        return $sanctumTokenTable->renderTable();
    }

    public function create()
    {
        PageTitle::setTitle(trans('packages/api::sanctum-token.create'));

        return SanctumTokenForm::create()->renderForm();
    }

    public function store(StoreSanctumTokenRequest $request): BaseHttpResponse
    {
        $accessToken = $request->user()->createToken($request->input('name'));

        session()->flash('plainTextToken', $accessToken->plainTextToken);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('api.sanctum-token.index'))
            ->setNextUrl(route('api.sanctum-token.index'))
            ->withCreatedSuccessMessage();
    }

    public function destroy(string $id): BaseHttpResponse
    {
        try {
            PersonalAccessToken::findOrFail($id)->delete();

            return $this
                ->httpResponse()
                ->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
