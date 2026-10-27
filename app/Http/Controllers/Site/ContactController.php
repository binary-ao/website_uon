<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot: bots preenchem o campo escondido; responde como sucesso sem guardar.
        if (filled($request->input('website'))) {
            return back()->with('contact_success', true);
        }

        $subjects = Settings::lines('contact_subjects');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9 +()\-]*$/'],
            'subject' => array_filter(['required', 'string', 'max:150', $subjects ? Rule::in($subjects) : null]),
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'consent' => ['accepted'],
        ], [], [
            'name' => 'nome', 'email' => 'email', 'phone' => 'telefone', 'subject' => 'assunto',
            'message' => 'mensagem', 'consent' => 'consentimento',
        ]);

        $message = new ContactMessage([
            'name' => strip_tags($data['name']),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => strip_tags($data['subject']),
            'message' => strip_tags($data['message']),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);
        $message->save();

        if ($recipient = Settings::get('contact_form_recipient')) {
            try {
                Mail::to($recipient)->send(new ContactMessageReceived($message));
            } catch (\Throwable $e) {
                // A mensagem fica sempre registada no CMS, mesmo sem email configurado.
                Log::warning('Falha ao enviar notificação de contacto: '.$e->getMessage());
            }
        }

        return back()->with('contact_success', true);
    }
}
