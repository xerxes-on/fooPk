<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use AdminSection;
use App\Enums\Admin\Permission\PermissionEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for Media Library.
 *
 * @package App\Http\Controllers\Admin
 */
final class MediaLibraryAdminController extends Controller
{
    public function index(Request $request): RedirectResponse|View
    {
        if (!$request->user()?->hasPermissionTo(PermissionEnum::SEE_ALL_MEDIA_LIBRARY->value)) {
            return redirect()->back();
        }

        $content = 'Media library page (In development)';
        return AdminSection::view($content, 'Media library');
    }

    public function import(Request $request): RedirectResponse|View
    {
        if (!$request->user()?->hasPermissionTo(PermissionEnum::IMPORT_MEDIA_LIBRARY->value)) {
            return redirect()->back();
        }

        $content = 'Import page (In development)';
        return AdminSection::view($content, 'Import');
    }

    public function showElfinder(): View
    {
        $locale = session()->get('translatable_lang', 'de');
        return AdminSection::view(view('admin::elfinder', compact('locale')), 'File Manager');
    }
}
