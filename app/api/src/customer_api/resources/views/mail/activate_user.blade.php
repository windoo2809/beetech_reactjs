@extends('mail.layout')
@section('content')
    <section>
        @if(!empty($customer_name))
            <p>{{ $customer_name }}</p>
        @endif
        @if(!empty($customer_branch_name))
            <p>{{ $customer_branch_name }}</p>
        @endif
        <p>{{ $customer_user_name }}様</p>
        <p>この度は【駐車場見積依頼サービス】をご利用ありがとうございます。</p>
        <p>【駐車場見積依頼サービス】のユーザー登録が完了致しましたのでご連絡致します。</p>
        <p>ご利用を開始するには下記URLにアクセスし、パスワードの登録を行い、<br>
            サービスにログインしてください。<br>
            ログインID：{{ $mail_address }}
        </p>
        <p>１．下記URLへアクセスする。</p>
        <p>URL：<a href="{{ $page_url }}" target="_blank">{{ $page_url }}</a></p>
        <p>２．パスワードの登録を行う。</p>
    </section>
@stop

@section('style')
@stop
