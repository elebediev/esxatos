<?php

namespace App\Http\Controllers;

use App\Models\Book;

class ModuleController extends ContentController
{
    protected string $contentType = Book::TYPE_MODULE;
    protected string $viewPrefix = 'modules';
    protected string $titleSingular = 'Модуль';
    protected string $titlePlural = 'Модули BibleQuote';
    protected int $rootCategoryId = 2; // МОДУЛИ BIBLEQUTE
}
