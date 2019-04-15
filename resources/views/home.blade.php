@extends('layouts.app')

@section('content')
    <div class="container" style="background-color: white;">
        <br>
        <table class="table table-bordered">
            <thead>
            <tr>
                <td>Парсеры</td>
            </tr>
            </thead>
            <tr>
                <td>
                    <a href="{{action('BlogsParserController@index')}}">Парсер блогов</a><br>
                    <a href="{{action('BlogsParserController@dead_profiles')}}">Парсер мёртвых профилей</a>
                </td>
            </tr>
        </table>
    </div>
@endsection
