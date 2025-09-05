@extends('welcome')
@section('datos')
@section('title', 'Secondary coating')
@livewire('tscoating-component', ['editable' => $editable, 'tipod' => $tipod])
@endsection