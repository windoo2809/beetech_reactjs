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
        <br>
        <p>この度は【駐車場見積依頼サービス】をご利用ありがとうございます。</p>
        <p>ユーザー情報更新用のCSVファイル取込みの結果をご連絡いたします。</p>
        <br>
        <p>CSVファイル名：{{ $file_name }}</p>
        <p>対象件数：{{ $total_record }}件</p>
        <p>更新件数：{{ $total_record_process }}件</p>
        <p>エラー件数：{{ $count_errors }}件</p>
        <p>備考：{{ $message_error }}</p>
        <p>【エラー対象データ】</p>
        <ul>
            @if($data_errors && count($data_errors) > 0)
                @foreach ($data_errors as $index => $errors)
                    @if(is_array($errors) && count($errors))
                        @foreach($errors as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    @else
                        <li>{{ $errors }}</li>
                    @endif
                @endforeach
            @endif
        </ul>
    </section>
@stop

@section('style')
@stop
