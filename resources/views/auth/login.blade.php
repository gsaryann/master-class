<!DOCTYPE html>
<html>
<head>
	<title>Вход | ОчУмелые ручки</title>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/styles.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/responsive.css') }}">
</head>
<body>
	<div class="header">
		<div class="row grid middle between">
			<div class="logo">
				<a href="{{ route('home') }}"><img src="{{ asset('img/logo.png') }}"></a>
			</div>
			<div class="title">
				Клуб любителей творчества «ОчУмелые ручки»
			</div>
			<div class="auth">
				<a href="{{ route('register') }}">Регистрация</a>
			</div>
		</div>
	</div>
	<div class="row row--nogutter">
		<div class="menu-burger">
			<div class="burger">
				<div></div>
				<div></div>
				<div></div>
			</div>
		</div>	
	</div>
	<div class="row row--nogutter top-line">
		<div class="line"></div>
	</div>
	<div class="main">
		<div class="row">
			<div class="row--small">
				@if(session('status') || $errors->any())
					<div class="message{{ $errors->any() ? ' message--error' : '' }}">
						<button type="button" class="message-close" aria-label="Закрыть уведомление" onclick="this.parentElement.classList.add('message-hidden'); setTimeout(() => this.parentElement.remove(), 250);">×</button>
						@if(session('status'))
							<div>{{ session('status') }}</div>
						@endif
						@foreach($errors->all() as $error)
							<div>{{ $error }}</div>
						@endforeach
					</div>
				@endif
				<form method="post" action="{{ route('login.submit') }}">
					@csrf
					<h2>Форма авторизации</h2>
					<div class="form-group">
						<label>Email</label>
						<input type="email" name="email" value="{{ old('email') }}" class="@error('email') field-error @enderror">
					</div>
					<div class="form-group">
						<label>Пароль</label>
						<input type="password" name="password" class="@error('password') field-error @enderror">
					</div>
					<div class="form-group">
						<button class="btn">Войти</button>
					</div>
				</form>
				<p><a href="{{ route('register') }}">Нет аккаунта? Создайте!</a></p>
				
			</div>
		</div>	
	</div>
	<div class="row row--nogutter">
		<div class="line"></div>
	</div>
	<div class="footer">
		<div class="row">
			<div class="row--small grid between">
				<div class="address">Наш адрес: ВДНХ, 120в</div>
				<div class="tel">Тел: 89123456765</div>
				<div class="copy">(с) Copyright, 2017</div>
			</div>
		</div>
	</div>
	<script>
		setTimeout(() => {
			document.querySelectorAll('.message').forEach((message) => {
				message.classList.add('message-hidden');
				setTimeout(() => message.remove(), 250);
			});
		}, 5000);
	</script>
</body>
</html>
