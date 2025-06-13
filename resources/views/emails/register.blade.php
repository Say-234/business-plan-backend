<x-mail::message>
# Introduction

The body of your message.

<x-mail::table>
    | Identifiant | Mot de passe |
    | ----------- | ------------ |
    |{{ $email }} |{{ $password }}|
</x-mail::table>

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
