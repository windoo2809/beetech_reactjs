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
        <p>【駐車場見積依頼サービス】にてログインURLが更新されました。</p>
        <br>
        <p>ご利用を開始するには下記URLにアクセスし、パスワードの登録を行い、</p>
        <p>サービスにログインしてください。</p>
        <br>
        <p>ログインID：{{ $mail_address }}</p>
        <br>
        <p>１．下記URLへアクセスする。</p>
        <p>URL：<a href="{{ $page_url }}" target="_blank">{{ $page_url }}</a></p>
    </section>
@stop

@section('style')
@stop
