<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Activity Information')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Date & Time')
                            ->dateTime('d M Y H:i:s'),

                        TextEntry::make('causer.name')
                            ->label('User')
                            ->placeholder('System'),

                        TextEntry::make('event')
                            ->label('Event')
                            ->badge()
                            ->formatStateUsing(
                                fn (?string $state): string => ucfirst($state ?? '-')
                            ),

                        TextEntry::make('subject_type')
                            ->label('Module')
                            ->formatStateUsing(
                                fn (?string $state): string => $state
                                    ? class_basename($state)
                                    : '-'
                            ),

                        TextEntry::make('subject_id')
                            ->label('Record ID'),
                    ])
                    ->columns(2),

                Section::make('Old Values')
                    ->schema([
                        KeyValueEntry::make('properties.old')
                            ->label('Before'),
                    ])
                    ->visible(fn ($record): bool =>
                        ! empty($record?->properties?->get('old'))
                    ),

                Section::make('New Values')
                    ->schema([
                        KeyValueEntry::make('properties.attributes')
                            ->label('After'),
                    ])
                    ->visible(fn ($record): bool =>
                        ! empty($record?->properties?->get('attributes'))
                    ),
            ]);
    }
}