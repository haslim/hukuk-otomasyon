<?php

namespace App\Controllers;

use App\Support\AuthContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProfileController extends Controller
{
    public function me(Request $request, Response $response): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        return $this->json($response, [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'title' => $user->title ?? null,
            'avatarUrl' => $user->avatar_url ?? null,
        ]);
    }

    public function update(Request $request, Response $response): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $data = (array) $request->getParsedBody();

        if (isset($data['name']) && is_string($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['title']) && is_string($data['title'])) {
            $user->title = $data['title'];
        }

        if (isset($data['avatarUrl']) && is_string($data['avatarUrl'])) {
            $user->avatar_url = $data['avatarUrl'];
        }

        // E-mail değişimine izin vermek istemezsek bu satırı kaldırabiliriz.
        if (isset($data['email']) && is_string($data['email'])) {
            $user->email = $data['email'];
        }

        $user->save();

        return $this->json($response, [
            'message' => 'Profil güncellendi',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'title' => $user->title ?? null,
                'avatarUrl' => $user->avatar_url ?? null,
            ],
        ]);
    }

    public function settings(Request $request, Response $response): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        return $this->json($response, [
            'settings' => [
                'notifications' => [
                    'emailNotifications' => $user->email_notifications ?? true,
                    'pushNotifications' => $user->push_notifications ?? true,
                    'caseUpdates' => $user->case_updates ?? true,
                    'taskReminders' => $user->task_reminders ?? true,
                ],
                'appearance' => [
                    'theme' => $user->theme ?? 'light',
                    'language' => $user->language ?? 'tr',
                    'timezone' => $user->timezone ?? 'Europe/Istanbul',
                ],
                'privacy' => [
                    'showProfileToOthers' => $user->show_profile ?? true,
                    'showOnlineStatus' => $user->show_online_status ?? true,
                ],
            ]
        ]);
    }

    public function updateSettings(Request $request, Response $response): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $data = (array) $request->getParsedBody();
        $settings = $data['settings'] ?? [];

        // Notification settings
        if (isset($settings['notifications'])) {
            $notifications = $settings['notifications'];
            if (isset($notifications['emailNotifications'])) {
                $user->email_notifications = $notifications['emailNotifications'];
            }
            if (isset($notifications['pushNotifications'])) {
                $user->push_notifications = $notifications['pushNotifications'];
            }
            if (isset($notifications['caseUpdates'])) {
                $user->case_updates = $notifications['caseUpdates'];
            }
            if (isset($notifications['taskReminders'])) {
                $user->task_reminders = $notifications['taskReminders'];
            }
        }

        // Appearance settings
        if (isset($settings['appearance'])) {
            $appearance = $settings['appearance'];
            if (isset($appearance['theme'])) {
                $user->theme = $appearance['theme'];
            }
            if (isset($appearance['language'])) {
                $user->language = $appearance['language'];
            }
            if (isset($appearance['timezone'])) {
                $user->timezone = $appearance['timezone'];
            }
        }

        // Privacy settings
        if (isset($settings['privacy'])) {
            $privacy = $settings['privacy'];
            if (isset($privacy['showProfileToOthers'])) {
                $user->show_profile = $privacy['showProfileToOthers'];
            }
            if (isset($privacy['showOnlineStatus'])) {
                $user->show_online_status = $privacy['showOnlineStatus'];
            }
        }

        $user->save();

        return $this->json($response, [
            'message' => 'Ayarlar güncellendi',
            'settings' => [
                'notifications' => [
                    'emailNotifications' => $user->email_notifications ?? true,
                    'pushNotifications' => $user->push_notifications ?? true,
                    'caseUpdates' => $user->case_updates ?? true,
                    'taskReminders' => $user->task_reminders ?? true,
                ],
                'appearance' => [
                    'theme' => $user->theme ?? 'light',
                    'language' => $user->language ?? 'tr',
                    'timezone' => $user->timezone ?? 'Europe/Istanbul',
                ],
                'privacy' => [
                    'showProfileToOthers' => $user->show_profile ?? true,
                    'showOnlineStatus' => $user->show_online_status ?? true,
                ],
            ]
        ]);
    }
}
