Nova mensagem recebida através do formulário de contacto do website.

Nome: {{ $contactMessage->name }}
Email: {{ $contactMessage->email }}
Telefone: {{ $contactMessage->phone ?: '—' }}
Assunto: {{ $contactMessage->subject }}
Data: {{ $contactMessage->created_at->format('d/m/Y H:i') }}

{{ $contactMessage->message }}

—
Mensagem registada no CMS: {{ route('admin.messages.show', $contactMessage) }}
