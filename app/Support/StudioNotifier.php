<?php

namespace App\Support;

use App\Models\Asset;
use App\Models\ExportRun;
use App\Models\Lead;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StudioNotifier
{
    public function newLead(Lead $lead): void
    {
        $this->sendToUsers(
            fn (User $user): bool => $user->can('leads.view'),
            fn (User $user): Notification => Notification::make()
                ->success()
                ->title($user->locale === 'en' ? 'New project enquiry' : 'طلب مشروع جديد')
                ->body(($user->locale === 'en' ? 'A new enquiry was received from ' : 'تم استلام طلب جديد من ').$lead->name)
                ->actions([
                    Action::make('view')
                        ->label($user->locale === 'en' ? 'Open enquiry' : 'فتح الطلب')
                        ->url(url('/admin/leads/'.$lead->getKey().'/edit'))
                        ->markAsRead(),
                ]),
        );
    }

    public function assetFailed(Asset $asset): void
    {
        $this->sendToUsers(
            fn (User $user): bool => $user->can('assets.update'),
            fn (User $user): Notification => Notification::make()
                ->danger()
                ->title($user->locale === 'en' ? 'Media processing failed' : 'فشلت معالجة ملف الميديا')
                ->body(($user->locale === 'en' ? 'Review the file: ' : 'راجع الملف: ').$asset->titleText($user->locale))
                ->actions([
                    Action::make('view')
                        ->label($user->locale === 'en' ? 'Open media' : 'فتح الميديا')
                        ->url(url('/admin/assets/'.$asset->getKey().'/edit'))
                        ->markAsRead(),
                ]),
        );
    }

    public function exportCompleted(ExportRun $run): void
    {
        $this->sendToOne($run->user_id, fn (User $user): Notification => Notification::make()
            ->success()
            ->title($user->locale === 'en' ? 'Excel export is ready' : 'ملف Excel جاهز')
            ->body($user->locale === 'en' ? 'The requested export has finished successfully.' : 'اكتمل تجهيز التصدير المطلوب بنجاح.')
            ->actions([
                Action::make('download')
                    ->label($user->locale === 'en' ? 'Download' : 'تحميل')
                    ->url(route('exports.download', $run))
                    ->markAsRead(),
            ]));
    }

    public function exportFailed(ExportRun $run): void
    {
        $this->sendToOne($run->user_id, fn (User $user): Notification => Notification::make()
            ->danger()
            ->title($user->locale === 'en' ? 'Excel export failed' : 'فشل تصدير Excel')
            ->body($user->locale === 'en' ? 'Please review the export and try again.' : 'راجع عملية التصدير وحاول مرة أخرى.'));
    }

    private function sendToOne(?int $userId, callable $notification): void
    {
        if ($userId && ($user = User::where('is_active', true)->find($userId))) {
            $this->deliver($user, $notification($user));
        }
    }

    private function sendToUsers(callable $filter, callable $notification): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        User::query()->where('is_active', true)->get()
            ->filter($filter)
            ->each(fn (User $user) => $this->deliver($user, $notification($user)));
    }

    private function deliver(User $user, Notification $notification): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        try {
            $notification->sendToDatabase($user, isEventDispatched: true);
        } catch (Throwable $error) {
            report($error);
        }
    }
}
