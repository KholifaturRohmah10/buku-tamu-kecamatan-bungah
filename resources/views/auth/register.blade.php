@extends('layouts.app')

@section('title', 'Daftar Tamu')

@section('content')
    @include('auth.portal', [
        'authModal' => $authModal ?? 'guest',
        'formFocus' => $formFocus ?? 'register',
    ])
@endsection
