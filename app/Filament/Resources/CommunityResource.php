<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityResource\Pages;
use App\Filament\Resources\CommunityResource\RelationManagers;
use App\Models\Community;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Section::make('Community Information')
                    ->description('Community Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->placeholder('Community Name')
                            ->unique()
                            ->autocapitalize()
                            ->required()
                            ->label('Community Name')
                            ->maxLength(255),
                        RichEditor::make('description')
                            ->placeholder('Community Description')
                            ->required()
                            ->label('Community Description')
                            ->maxLength(65535),
                    ]),
                Section::make("Account Information")
                    ->description('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('account_name')
                            ->placeholder('Account Name')
                            ->required()
                            ->live()
                            ->label('Account Name')
                            ->debounce(1000)
                            ->autocapitalize()
                            ->maxLength(255)
                            ->afterStateUpdated(
                                function (callable $set, $state) {
                                    //incase the account name is set then set the account number
                                    $account_number =  Str::random(20);
                                    $set('account_number', $account_number);
                                }
                            ),
                        Forms\Components\TextInput::make('account_number')
                            ->placeholder('Account Number')
                            ->required()
                            ->label('Account Number')
                            // ->disabled()
                            ->unique()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_balance')
                            ->prefix("UGX")
                            ->required()
                            ->disabled()
                            ->default(0)
                            ->label('Account Balance')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                //community leader
                Section::make("Community Leader Information")
                    ->description('Community Leader Information')
                    ->schema([
                        Forms\Components\Select::make('leader_id')
                            ->label('Community Leader')
                            ->required()
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->copyable()
                    ->copyMessage('name  copied')
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label('Community Name'),
                TextColumn::make('description')
                    ->copyable()
                    ->copyMessage('description  copied')
                    ->wrap()
                    ->toggleable()
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->sortable()
                    ->label('Community Description')
                    ->html(),
                TextColumn::make('is_active')
                    ->formatStateUsing(function ($state) {
                        return $state ? 'Active' : 'In active';
                    })
                    ->color(function (Community $record) {
                        return $record->is_active ? 'success' : 'danger';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label('Community Status'),
                TextColumn::make('account_name')
                    ->copyable()
                    ->copyMessage('account name  copied')
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->label('Account Name'),
                TextColumn::make('account_number')
                    ->copyable()
                    ->copyMessage('account number  copied')
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->label('Account Number'),
                TextColumn::make('account_balance')
                    ->copyable()
                    ->copyMessage('account balance  copied')
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->toggleable()
                    ->money('UGX')
                    ->sortable()
                    ->label('Account Balance')
            ])
            ->filters([
                //Tables\Filters\TrashedFilter::make(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make("Activation")
                    ->action(
                        function (Community $record) {
                            $record->update([
                                'is_active' => !$record->is_active
                            ]);
                            Notification::make()
                                ->title('Community status updated')
                                ->success()
                                ->send();
                        }
                    )
                    ->color(function (Community $record) {
                        return $record->is_active ? 'danger' : 'success';
                    })
                    ->label(function (Community $record) {
                        return $record->is_active ? 'Deactivate' : 'Activate';
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommunities::route('/'),
            'create' => Pages\CreateCommunity::route('/create'),
            'edit' => Pages\EditCommunity::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
