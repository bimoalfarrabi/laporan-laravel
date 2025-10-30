@props(['date'])

@if($date->diffInHours(now()) >= 1)
    {{ $date->locale('id')->translatedFormat('d F Y, H:i') }}
@else
    {{ $date->locale('id')->diffForHumans() }}
@endif
