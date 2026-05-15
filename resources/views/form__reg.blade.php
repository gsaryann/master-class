<!DOCTYPE html>
<html>
<head>
	<title>Регистрация | ОчУмелые ручки</title>
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
				<a href="{{ route('login') }}">Вход</a>
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
				<form method="post" action="{{ route('register.submit') }}" enctype="multipart/form-data">
					@csrf
					<h2>Форма регистрации</h2>
					<div class="form-group">
						<label>ФИО</label>
						<input type="text" name="name" value="{{ old('name') }}" class="@error('name') field-error @enderror">
					</div>
					<div class="form-group">
						<label>Email</label>
						<input type="email" name="email" value="{{ old('email') }}" class="@error('email') field-error @enderror">
					</div>
					<div class="form-group">
						<label>Пароль</label>
						<input type="password" name="password" class="@error('password') field-error @enderror">
					</div>
					<div class="form-group">
						<label>Подтверждение пароля</label>
						<input type="password" name="password_confirmation" class="@error('password_confirmation') field-error @enderror">
					</div>
					<div class="form-group">
						<label>Номер телефона</label>
						<input type="tel" name="phone" value="{{ old('phone') }}" class="@error('phone') field-error @enderror">
					</div>
					<div class="form-group">
						<label>Фото (необязательно)</label>
						<input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="@error('photo') field-error @enderror">
					</div>
					<div class="form-group">
						<button class="btn">Отправить</button>
					</div>
				</form>
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
