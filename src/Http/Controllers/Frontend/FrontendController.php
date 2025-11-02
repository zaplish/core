<?php

namespace Zaplish\Core\Http\Controllers\Frontend;

use Zaplish\Core\Http\Controllers\Controller;
use Zaplish\Core\Models\Content;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FrontendController extends Controller
{
    /**
     * Render the homepage.
     */
    public function home(Request $request)
    {
        // Find homepage by "is_homepage" in JSON settings
        $page = Content::where('settings->is_homepage', true)->first();

        if (!$page) {
            // TODO show instructions to create a homepage
            abort(404, 'Homepage not found.');
        }

        return $this->renderPage($page);
    }

    /**
     * Catch-all route for all other pages.
     */
    public function page(Request $request, ?string $slug = null)
    {
        $slug = trim($slug ?: '', '/');

        $page = Content::with('contentType')->where('slug', $slug)->first();

        if (!$page) {
            // TODO test
            // Optional: render theme-specific 404
            if (View::exists('theme::404')) {
                return response()->view('theme::404', [], 404);
            }
            throw new NotFoundHttpException("Page not found: {$slug}");
        }

        return $this->renderPage($page);
    }

    /**
     * Shared renderer for any content page.
     */
    protected function renderPage(Content $page)
    {
        $contentType = $page->contentType->key ?? $page->contentType ?? 'page';
        $slug = $page->slug;

        // Try most specific to least specific template
        $viewCandidates = [
            "theme::{$contentType}-{$slug}",
            "theme::{$slug}",
            "theme::{$contentType}",
            "theme::page",
        ];

        foreach ($viewCandidates as $view) {
            if (View::exists($view)) {
                return view($view, compact('page', 'contentType'));
            }
        }

        // As last resort, throw 404
        // TODO
        throw new NotFoundHttpException("No matching view found for {$slug}");
    }
}
