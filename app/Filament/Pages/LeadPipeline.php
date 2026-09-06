<?php

namespace App\Filament\Pages;

use App\Models\Lead;
use App\Support\Studio;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LeadPipeline extends Page
{
    protected string $view = 'filament.pages.lead-pipeline';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected static ?int $navigationSort = 1;

    public string $search = '';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('pipeline');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return Studio::text('clients');
    }

    public function getTitle(): string
    {
        return self::getNavigationLabel();
    }

    public function move(int $id, string $status): void
    {
        abort_unless(auth()->user()?->can('leads.update') && in_array($status, ['new', 'contacted', 'quoted', 'won', 'lost']), 403);
        Lead::findOrFail($id)->update(['status' => $status]);
        Notification::make()->success()->title(Studio::text('updated'))->send();
    }

    public function leads(string $status)
    {
        return Lead::where('status', $status)->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))->with('service')->latest()->limit(30)->get();
    }
}
