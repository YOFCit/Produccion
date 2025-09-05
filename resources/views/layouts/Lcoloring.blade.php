@extends('welcome')
@section('datos')
@section('title', 'Coloring')
@livewire('tcoloring-component', ['editable' => $editable])
@endsection