@extends('welcome')
@section('datos')
@section('title', 'Sheating')
@livewire('tsheating-component', ['editable' => $editable, 'tipod' => $tipod])
@endsection