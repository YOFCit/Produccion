@extends('welcome')
@section('datos')
@section('title', 'Stranding')
@livewire('tstranding-component', ['editable' => $editable, 'tipod' => $tipod])
@endsection