<?php

namespace App\Filament\Resources\CommunityResource\Pages;

use App\Filament\Resources\CommunityResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;


class CreateCommunity extends CreateRecord
{
    protected static string $resource = CommunityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Community registered successfully';
    }

    protected function afterCreate(): void
    {
        // Runs after the form fields are saved to the database.
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Community registered successfully')
            ->body('The community has been registered successfully');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data =  static::getModel()::create($data);
        User::where("id", $data->leader_id)->update(["community_id" => $data->id]);
        return $data;
    }
}
