<!DOCTYPE html>
<html>
<head>
	<title>Главная | ОчУмелые ручки</title>
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
				@if($sessionUser)
					@if(($sessionUser['role'] ?? '') === 'master')
						<a href="{{ route('cabinet') }}">Личный кабинет</a> | <a href="{{ route('logout') }}">Выход</a>
					@else
						<a href="{{ route('cabinet') }}">Личный кабинет</a> | <a href="{{ route('logout') }}">Выход</a>
					@endif
				@else
					<a href="{{ route('login') }}">Вход/Регистрация</a>
				@endif
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
	<div class="main">
		<div class="row">
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
			<div class="hover"></div>
			<div class="title">ОчУмелые ручки</div>
		</div>
		<div class="row">
			@if($sessionUser && ($sessionUser['role'] ?? '') === 'visitor')
				<div class="home-bookings-panel">
					<div class="home-bookings-panel__title">Мои записи</div>
					@forelse($userBookings as $booking)
						<div class="home-booking">
							<b>{{ \Illuminate\Support\Str::limit($booking->title, 120) }}</b><br>
							{{ $booking->craftType->name }}<br>
							{{ $booking->master->name }}<br>
							{{ $booking->scheduled_date->format('d.m.Y') }} {{ $booking->time_label }}
							<a href="{{ route('booking.cancel', ['masterClass' => $booking, 'source' => 'home']) }}" class="booking-link">Отменить запись</a>
						</div>
					@empty
						<p>Вы пока не записаны ни на один мастер-класс.</p>
					@endforelse
				</div>
			@endif
			<div class="row--small grid between">
				<div class="content home-content">
					<img src="{{ asset('img/architectural-model-pictures-cool-architectural-model-.jpg') }}">
					<h2>О компании</h2>
					<p>Клуб любителей творчества «ОчУмелые ручки» проводит мастер-классы для детей и взрослых по разным направлениям декоративно-прикладного искусства. Мы помогаем участникам раскрыть творческие способности, получить новые практические навыки и провести время с пользой.</p>
					<p>В клубе работают опытные ведущие, которые составляют расписание занятий, проводят мастер-классы в небольших группах и сопровождают участников на каждом этапе работы. Каждое занятие рассчитано на понятный результат: готовое изделие, новый навык и позитивный опыт творчества.</p>
					<p><span>Выберите интересующий вид творчества</span> в меню справа, чтобы посмотреть описание направления и расписание ближайших мастер-классов.</p>

				</div>
				<ul class="menu">
					<li class="menu-title">Виды творчества</li>
					@foreach($craftTypes as $craftType)
						<li><a href="{{ route('craft-types.show', $craftType) }}">{{ $craftType->name }}</a></li>
					@endforeach
				</ul>
			</div>

			<div class="row shedule">
				<div class="row--small">
					<h2>Ближайшие мастер-классы</h2>
					<div class="drivers">
						@forelse($upcomingMasterClasses as $masterClass)
							<div class="driver grid">
								<div class="driver-left grid">
									<div class="driver-photo">
										<img src="{{ asset('img/driver1.png') }}">
									</div>
									<div class="driver-text">
										<div class="driver-name">{{ $masterClass->master?->name }}</div>
										<div class="driver-desc">
											<b>{{ \Illuminate\Support\Str::limit($masterClass->title, 120) }}</b><br>
											{{ $masterClass->craftType->name }}<br>
											{{ \Illuminate\Support\Str::limit($masterClass->description, 500) }}<br>
											Стоимость: {{ $masterClass->price }} руб.<br>
											Свободных мест: {{ $masterClass->free_places }}
										</div>
									</div>
								</div>
								<div class="driver-right">
									@if(!$sessionUser)
										<a href="{{ route('login') }}" class="btn btn--light">Записаться</a>
									@elseif(($sessionUser['role'] ?? '') === 'master')
										<div class="driver-note">Запись доступна посетителям</div>
									@elseif($masterClass->is_expired)
										<div class="driver-note">Мастер-класс завершён</div>
									@elseif($masterClass->free_places < 1)
										<div class="driver-note">Свободных мест нет</div>
									@elseif($userBookings->contains('id', $masterClass->id))
										<div class="driver-note">Вы уже записаны</div>
										<a href="{{ route('booking.cancel', ['masterClass' => $masterClass, 'source' => 'home']) }}" class="booking-link booking-link--light">Отменить запись</a>
									@else
										<a href="{{ route('booking.confirm', $masterClass) }}" class="btn btn--light">Записаться</a>
									@endif
									<div class="driver-time">{{ $masterClass->scheduled_date->format('d.m.Y') }} {{ $masterClass->time_label }}</div>
								</div>
							</div>
						@empty
							<p>Мастер-классов пока нет.</p>
						@endforelse
					</div>
				</div>
			</div>
		</div>
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
