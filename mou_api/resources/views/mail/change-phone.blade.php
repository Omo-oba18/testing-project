@component('mail::message')
# Hi {{ $notifiable->name }},

{{ $dataContent['content'] }}

@component('mail::button', ['url' => $link])
Change Phone
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
