<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>@yield('title','sample app') --laravel 入门教程</title>
	<link rel="stylesheet" href="/css/app.css">
</head>
<body>
  @include('layouts._header')
<div class="container" id="app">
	@include('shared._messages')
	@yield('content')
  	@include('layouts._footer')
</div>
<script src="/js/app.js"></script>
</body>
</html>