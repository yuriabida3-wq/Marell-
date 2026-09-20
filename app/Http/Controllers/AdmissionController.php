<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdmissionController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'student_name'   => 'required|string|max:120',
            'dob'            => 'nullable|date',
            'class_applying' => 'required|string|max:30',
            'parent_name'    => 'required|string|max:120',
            'parent_phone'   => 'required|string|max:20',
            'parent_email'   => 'nullable|email|max:120',
            'message'        => 'nullable|string|max:2000',
            'consent'        => 'accepted',
        ]);

        $data['consent'] = true;
        $data['status']  = 'new';

        $admission = Admission::create($data);

        // Email notification (logged in dev; real send in prod)
        try {
            if ($admission->parent_email) {
                Mail::raw(
                    "Dear {$admission->parent_name},\n\nWe have received your application for {$admission->student_name} to join {$admission->class_applying}.\n\nOur admissions team will contact you within 48 hours.\n\nMarell Academy",
                    function ($m) use ($admission) {
                        $m->to($admission->parent_email)->subject('Application Received — Marell Academy');
                    }
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Admission email failed: ' . $e->getMessage());
        }

        // SMS notification (queued; safe if AT not configured)
        try {
            $phone = $this->normalizePhone($admission->parent_phone);
            if ($phone) {
                app(\App\Services\SmsService::class)->send(
                    $phone,
                    "Marell Academy: Application received for {$admission->student_name}. We will contact you within 48hrs."
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Admission SMS failed: ' . $e->getMessage());
        }

        return redirect()->route('admissions')
            ->with('success', 'Application received! We will contact you within 48 hours.');
    }

    protected function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0'))   $phone = '254' . substr($phone, 1);
        if (str_starts_with($phone, '7'))   $phone = '254' . $phone;
        if (str_starts_with($phone, '1'))   $phone = '254' . $phone;
        return strlen($phone) >= 12 ? $phone : null;
    }
}
