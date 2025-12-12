<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CacheController extends Controller
{
    /**
     * Cache keys used in the application.
     */
    public const CACHE_KEYS = [
        'books_sidebar_categories' => 'Категорії книг (сайдбар)',
        'modules_sidebar_categories' => 'Категорії модулів (сайдбар)',
        'software_sidebar_categories' => 'Категорії софту (сайдбар)',
        'audio_sidebar_categories' => 'Категорії аудіо (сайдбар)',
    ];

    public function index(): View
    {
        $cacheItems = [];

        foreach (self::CACHE_KEYS as $key => $description) {
            $cacheItems[$key] = [
                'description' => $description,
                'exists' => Cache::has($key),
            ];
        }

        return view('admin.cache.index', compact('cacheItems'));
    }

    public function clear(string $key): RedirectResponse
    {
        if (!array_key_exists($key, self::CACHE_KEYS)) {
            return back()->with('error', 'Невідомий ключ кешу.');
        }

        Cache::forget($key);

        return back()->with('success', 'Кеш "' . self::CACHE_KEYS[$key] . '" успішно очищено.');
    }

    public function clearAll(): RedirectResponse
    {
        foreach (array_keys(self::CACHE_KEYS) as $key) {
            Cache::forget($key);
        }

        return back()->with('success', 'Весь кеш успішно очищено.');
    }
}
