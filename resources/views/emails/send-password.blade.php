<x-mail::message>
    Hello dear {{$full_name}} <br>
    Your Account Password is <b>{{$password}}</b>:  <br>
    Thanks, {{ config('app.name') }}
</x-mail::message>
