@component('mail::message')
# Hi Admin

You have a new feedback:
@component('mail::panel')
@if(!empty($feedback->user))
- Name: {{ $feedback->user->name }}
- Email: {{ $feedback->user->email }}
@endif
- Feedback: {{ $feedback->content }}
@endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent
