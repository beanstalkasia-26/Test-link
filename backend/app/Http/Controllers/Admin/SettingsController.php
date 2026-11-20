<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Models\Media;
use App\Models\Setting;
use App\Support\AuditLogger;
use App\Support\Fields;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        $values = SiteSettings::values();
        $mediaIds = Fields::mediaIds(SiteSettings::fields(), $values);

        return PageResponse::render('admin/Settings', [
            'fields' => Fields::forAdmin(SiteSettings::fields()),
            'values' => $values,
            'media' => Media::query()->whereIn('id', $mediaIds)->get()->mapWithKeys(fn ($m) => [$m->id => $m->toAdmin()]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(Fields::rules(SiteSettings::fields()));
        $data = Fields::normalize(SiteSettings::fields(), $validated);

        if (! empty($data['enquiry_recipients'])) {
            $emails = array_filter(array_map('trim', explode(',', $data['enquiry_recipients'])));
            foreach ($emails as $email) {
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return back()->withErrors(['enquiry_recipients' => "\"{$email}\" is not a valid email address."]);
                }
            }
        }

        DB::transaction(function () use ($data): void {
            foreach (SiteSettings::fields() as $field) {
                Setting::put($field['name'], $data[$field['name']] ?? null, $field['group']);
            }
        });

        AuditLogger::log('settings.updated', null, 'Updated global settings');

        return back()->with('success', 'Settings saved.');
    }
}
