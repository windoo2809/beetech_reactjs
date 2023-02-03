@extends('mail.layout')
@section('content')
    <section>
        <p>【駐車場見積依頼サービス】をご利用ありがとうございます。</p>
        <p>以下より、パスワード再設定ページへアクセスし、パスワードの初期設定を行ってください。</p>
        <p>URL：<a href="{{ $page_url }}" target="_blank">{{ $page_url }}</a></p>
        <p>URLの有効期限：{{ $expire }}</p>
    </section>
@stop
@section('style')
@stop
