<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
</head>
<body>
<div class="email-template" style="max-width: 992px">
    @yield('content')
    <footer style="border-top: 3px double #ccc; border-bottom: 3px double #ccc; margin-top: 15px; padding: 15px 0">
        <p>本メールに心当たりがない方はお手数ですが至急解除致しますのでお問合せください。</p>

        <p>尚、本メールは配信専用です。ご返信頂いても回答することが出来ません。</p>
    </footer>
    <p>株式会社ランドマーク</p>
</div>
@yield('style')
</body>
</html>
