<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">
		<style>
			table form { margin-bottom: 0; }
			form ul { margin-left: 0; list-style: none; }
			.error { color: red; font-style: italic; }
			body { padding-top: 20px; }
		</style>
        <link href="/css/game.css" rel="stylesheet" />
	</head>

	<body>

    <script type="text/javascript" src="/js/jquery-1.8.2.min.js"></script>
    <script type="text/javascript" src="/js/game/easeljs-0.5.0.min.js"></script>
    <script type="text/javascript" src="/js/game/preloadjs-0.2.0.min.js"></script>

    <script type="text/javascript" src="/js/events.js"></script>

    <script type="text/javascript" src="/js/index.js"></script>
    <script type="text/javascript" src="/js/ajax.js"></script>
    <script type="text/javascript" src="/js/modal.js"></script>
    <script type="text/javascript" src="/js/form.js"></script>
    <script type="text/javascript" src="/js/pages.js"></script>

    <script type="text/javascript" src="/js/game/canvasActions.js"></script>
    <script type="text/javascript" src="/js/game/IL.Map.js"></script>
    <script type="text/javascript" src="/js/game/IL.Units.js"></script>
    <script type="text/javascript" src="/js/game/IL.Unit.js"></script>

		<div class="container">
			@if (Session::has('message'))
				<div class="flash alert">
					<p>{{ Session::get('message') }}</p>
				</div>
			@endif

			@yield('body')
		</div>

	</body>

</html>