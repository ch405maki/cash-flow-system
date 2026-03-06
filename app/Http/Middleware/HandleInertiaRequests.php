<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Models\Signatory;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        
        // Get ALL active signatories (not just the first one)
        $signatories = Signatory::where('status', 'active')->get();

        // Get notifications for authenticated user
        $user = $request->user();
        $notifications = [];
        $unreadCount = 0;

        if ($user) {
            // Get latest 5 unread notifications for the dropdown
            $notifications = $user->unreadNotifications()
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'data' => $notification->data,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'read_at' => $notification->read_at,
                    ];
                });

            $unreadCount = $user->unreadNotifications()->count();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user?->load('profilePicture'),
                'needs_to_accept_terms' => $user?->needsToAcceptTerms() ?? false,
            ],
            'signatories' => $signatories->map(function ($signatory) {
                return [
                    'full_name' => $signatory->full_name,
                    'position' => $signatory->position,
                ];
            })->toArray(),
            // Add notifications here
            'notifications' => [
                'list' => $notifications,
                'unread_count' => $unreadCount,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}