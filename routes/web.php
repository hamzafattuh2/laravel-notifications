<?php

use App\Http\Controllers\NotificationController;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
});

if (app()->environment('local')) {
    Route::get('/dev/login', function (Request $request) {
        $email = (string) $request->query('email', 'dev@example.com');
        $password = (string) $request->query('password', 'password');
        $name = (string) $request->query('name', 'Dev Tester');

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect('/')->with('status', "Logged in as {$user->email}");
    })->name('dev.login');

    Route::post('/dev/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Logged out.');
    })->middleware('auth')->name('dev.logout');

    Route::post('/dev/send-notification', function (Request $request, NotificationService $notificationService) {
        $notificationService->sendToUser($request->user(), [
            'title' => 'Test Notification',
            'message' => 'This is a local development notification generated for quick testing.',
            'type' => 'info',
            'action_url' => url('/'),
        ]);

        return redirect('/')->with('status', 'Test notification queued for your account.');
    })->middleware('auth')->name('dev.send-notification');
}
