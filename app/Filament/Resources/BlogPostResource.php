<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Filament\Resources\BlogPostResource\RelationManagers;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $slug = 'blog-posts';

    protected static ?string $navigationLabel = 'Blog Posts';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Post Information')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                    
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    
                    Forms\Components\Textarea::make('excerpt')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->columns(2),

            Forms\Components\Section::make('Content')
                ->schema([
                    Forms\Components\RichEditor::make('content')
                        ->required()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Settings')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                    
                    Forms\Components\Select::make('tags')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload(),
                    
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])
                        ->default('draft')
                        ->required(),
                    
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Publish Date'),
                    
                    Forms\Components\FileUpload::make('featured_image')
                        ->image()
                        ->directory('blog-images'),
                    
                    Forms\Components\Hidden::make('user_id')
                        ->default(auth()->id()),
                ])
                ->columns(2),
        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\ImageColumn::make('featured_image')
                ->label('Image')
                ->size(60),
            
            Tables\Columns\TextColumn::make('title')
                ->searchable()
                ->sortable()
                ->limit(50),

            Tables\Columns\TextColumn::make('slug')
                ->searchable()
                ->sortable()
                ->color('gray')
                ->toggleable()
                ->limit(30),
            
            Tables\Columns\TextColumn::make('category.name')
                ->searchable()
                ->sortable(),
            
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'secondary' => 'draft',
                    'success' => 'published',
                    'danger' => 'archived',
                ]),
            
            Tables\Columns\TextColumn::make('published_at')
                ->dateTime()
                ->sortable(),
            
            Tables\Columns\TextColumn::make('user.name')
                ->label('Author')
                ->sortable(),
            
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ]),
            
            Tables\Filters\SelectFilter::make('category')
                ->relationship('category', 'name'),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ])
        ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
