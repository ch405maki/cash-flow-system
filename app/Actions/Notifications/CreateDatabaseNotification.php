<?php

namespace App\Actions\Notifications;

use App\Models\User;
use Illuminate\Support\Str;

class CreateDatabaseNotification
{
    public function execute(
        User $notifiable,
        string $type,
        array $data,
    ): void {
        \DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $notifiable->id,
            'data' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function executeBatch(
        array $notifiables,
        string $type,
        array $data,
        ?\Closure $dataModifier = null,
    ): void {
        $records = [];

        foreach ($notifiables as $notifiable) {
            $notificationData = $dataModifier
                ? $dataModifier($notifiable, $data)
                : $data;

            $records[] = [
                'id' => (string) Str::uuid(),
                'type' => $type,
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $notifiable instanceof User ? $notifiable->id : $notifiable,
                'data' => json_encode($notificationData),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($records)) {
            \DB::table('notifications')->insert($records);
        }
    }
}
