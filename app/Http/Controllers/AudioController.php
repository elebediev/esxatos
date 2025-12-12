<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;

class AudioController extends ContentController
{
    protected string $contentType = Book::TYPE_AUDIO;
    protected string $viewPrefix = 'audio';
    protected string $titleSingular = 'Аудиокнига';
    protected string $titlePlural = 'Аудиокниги';
    protected int $rootCategoryId = 1; // Inside КНИГИ ЭСХАТОС, but we'll handle separately
}
