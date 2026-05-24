@extends('layouts.app')

@section('title', 'Profesores de Proyecto')
@section('header', 'Gestión de Profesores de Proyecto')

@section('content')
    <div style="background-color: #FFFFFF; border: 1px solid #CCC; margin: 10px; padding: 15px;">
        <livewire:project-professor-manager />
    </div>
@endsection
