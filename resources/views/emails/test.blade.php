Hello {{$user->name}}
Thank you for create an account. Please verify your email using this link :
{{ route('verify', [$user->verification_token]) }}

@component('mail::message')
# Introduction

The body of your message.

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
