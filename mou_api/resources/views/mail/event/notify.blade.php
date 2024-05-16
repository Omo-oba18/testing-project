@component('mail::message')
# Hi {{ $notifiable->name }},

{{ $content }}

@component('mail::button', ['url' => $link])
Open App
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
