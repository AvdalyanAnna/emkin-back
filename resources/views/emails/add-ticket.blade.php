<x-mail::message>
    Name: {{$name}} <br>
    Email: {{$email}} <br>
    @if(isset($theme) && $theme)
        Question Theme: {{$theme}} <br>
    @endif
    Question:  <br> {{$description}} <br>
    Thanks, {{ config('app.name') }}
</x-mail::message>
