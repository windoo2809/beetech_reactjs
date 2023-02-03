@extends('mail.layout')
@section('content')
    <section>
        @if(!empty($customer_name))
            <p>{{ $customer_name }}</p>
        @endif
        @if(!empty($customer_branch_name))
            <p>{{ $customer_branch_name }}</p>
        @endif
        @if(!empty($customer_user_name))
            <p>{{ $customer_user_name }}様</p>
        @endif

        <br>
        <p>Eメールアドレスが変更されましたので、お知らせいたします。</p>
        <p>変更を確定するために、下記URLにアクセスしてください。</p>
        <br>
        <p>ログインID：{{ $mail_address }}</p>
        <br>
        <p>１．下記URLへアクセスする。</p>
        <p>URL：<a href="{{ $page_url }}" target="_blank">{{ $page_url }}</a></p>
    </section>
@stop

@section('style')
@stop
