<?php

namespace App\Http\Controllers;

use App\Models\Book;

class SoftwareController extends ContentController
{
    protected string $contentType = Book::TYPE_SOFTWARE;
    protected string $viewPrefix = 'software';
    protected string $titleSingular = 'Программа';
    protected string $titlePlural = 'Программы';
    protected int $rootCategoryId = 3; // ПРОГРАММЫ
}
