<?php

namespace Tiryaq\Api\Tables;

use Tiryaq\Api\Models\PersonalAccessToken;
use Tiryaq\Table\Abstracts\TableAbstract;
use Tiryaq\Table\Actions\DeleteAction;
use Tiryaq\Table\BulkActions\DeleteBulkAction;
use Tiryaq\Table\Columns\Column;
use Tiryaq\Table\Columns\CreatedAtColumn;
use Tiryaq\Table\Columns\DateTimeColumn;
use Tiryaq\Table\Columns\IdColumn;
use Tiryaq\Table\Columns\NameColumn;
use Tiryaq\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class SanctumTokenTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->setView('packages/api::table')
            ->model(PersonalAccessToken::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('api.sanctum-token.create'))
            ->addAction(DeleteAction::make()->route('api.sanctum-token.destroy'))
            ->addColumns([
                IdColumn::make(),
                NameColumn::make(),
                Column::make('abilities')
                    ->label(trans('packages/api::sanctum-token.abilities')),
                DateTimeColumn::make('last_used_at')
                    ->label(trans('packages/api::sanctum-token.last_used_at')),
                CreatedAtColumn::make(),
            ])
            ->addBulkAction(DeleteBulkAction::make())
            ->queryUsing(fn (Builder $query) => $query->select([
                'id',
                'name',
                'abilities',
                'last_used_at',
                'created_at',
            ]));
    }
}
