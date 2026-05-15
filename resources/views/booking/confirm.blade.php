<!DOCTYPE html>
<html>
<head>
	<title>{{ ($mode ?? 'store') === 'cancel' ? 'Подтверждение отмены записи' : 'Подтверждение записи' }} | ОчУмелые ручки</title>
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
				<a href="{{ route('home') }}">Главная</a>
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
				<h2>{{ ($mode ?? 'store') === 'cancel' ? 'Подтверждение отмены записи' : 'Подтверждение записи' }}</h2>
				<p><b>ФИО пользователя:</b> {{ $user->name }}</p>
				<p><b>Вид творчества:</b> {{ $masterClass->craftType->name }}</p>
				<p><b>ФИО мастера:</b> {{ $masterClass->master->name }}</p>
				<p><b>Дата:</b> {{ $masterClass->scheduled_date->format('d.m.Y') }}</p>
				<p><b>Время:</b> {{ $masterClass->time_label }}</p>
				<form method="post" action="{{ ($mode ?? 'store') === 'cancel' ? route('booking.cancel.submit', $masterClass) : route('booking.store', $masterClass) }}">
					@csrf
					@if(($mode ?? 'store') === 'cancel')
						<input type="hidden" name="source" value="{{ $source ?? 'category' }}">
					@endif
					<button class="btn" type="submit">{{ ($mode ?? 'store') === 'cancel' ? 'Подтвердить отмену' : 'Подтвердить' }}</button>
					<a href="{{ route('craft-types.show', $masterClass->craftType) }}">Вернуться назад</a>
				</form>
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
