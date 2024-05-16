@component('mail::message')
# Hi {{ $user->name }},

<p>{{ $content }}</p>

@component('mail::button', ['url' => $link])
    Open App
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
