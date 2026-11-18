<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ComplaintController extends Controller
{
    public function store(Request $request, GraduationData $data): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'registration_number' => ['nullable', 'string', 'max:60'],
            'study_level' => ['nullable', 'string', 'max:80'],
            'programme' => ['nullable', 'string', 'max:80'],
            'country_code' => ['required', 'string', 'max:8', 'regex:/^\+\d{1,4}$/'],
            'phone' => ['required', 'digits:9'],
            'message' => ['required', 'string', 'max:1000'],
            'list_type' => ['nullable', 'string', 'in:graduands,best-students'],
            'searched_name' => ['nullable', 'string', 'max:120'],
        ]);

        $phoneDigits = $validated['phone'];
        $contact = trim($validated['country_code'].' '.$phoneDigits);

        $complaint = [
            'id' => (string) Str::uuid(),
            'full_name' => trim($validated['full_name']),
            'registration_number' => trim((string) ($validated['registration_number'] ?? '')),
            'study_level' => trim((string) ($validated['study_level'] ?? '')),
            'programme' => trim((string) ($validated['programme'] ?? '')),
            'country_code' => $validated['country_code'],
            'phone' => $phoneDigits,
            'contact' => $contact,
            'message' => trim($validated['message']),
            'list_type' => $validated['list_type'] ?? 'graduands',
            'searched_name' => trim((string) ($validated['searched_name'] ?? '')),
            'created_at' => now()->toIso8601String(),
            'ip' => $request->ip(),
        ];

        $data->saveComplaint($complaint);

        $this->notifySupport($complaint, $data);

        return response()->json([
            'ok' => true,
            'message' => 'Your complaint has been sent. The graduation desk will follow up.',
        ]);
    }

    protected function notifySupport(array $complaint, GraduationData $data): void
    {
        $to = $data->complaintEmail();

        if ($to === '') {
            return;
        }

        $subject = 'Name missing complaint — '.($complaint['list_type'] ?? 'list');
        $body = implode("\n", [
            'A graduation list complaint was submitted.',
            '',
            'Full name: '.$complaint['full_name'],
            'Registration: '.($complaint['registration_number'] ?: '—'),
            'Study level: '.($complaint['study_level'] ?: '—'),
            'Programme: '.($complaint['programme'] ?: '—'),
            'Phone: '.$complaint['contact'],
            'List: '.($complaint['list_type'] ?? '—'),
            'Searched as: '.($complaint['searched_name'] ?: '—'),
            '',
            'Message:',
            $complaint['message'],
            '',
            'Submitted: '.$complaint['created_at'],
        ]);

        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        } catch (Throwable) {
            // Complaint is already stored locally; email is best-effort.
        }
    }
}
