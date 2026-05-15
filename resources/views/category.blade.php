<!DOCTYPE html>
<html>
<head>
	<title>{{ $activeCraftType->name }} | ОчУмелые ручки</title>
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
						<a href="{{ route('cabinet') }}">Личный кабинет</a>
					@else
						<a href="{{ route('cabinet') }}">Личный кабинет</a>
					@endif
					| <a href="{{ route('logout') }}">Выход</a>
				@else
					<a href="{{ route('login') }}">Вход</a>
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
			<div class="title">{{ $activeCraftType->name }}</div>
			<div class="row--small grid between">
				<div class="content">
					<img src="{{ asset($activeCraftType->image ?: 'img/elifant.png') }}">
					{!! $activeCraftType->description !!}

				</div>
				<ul class="menu">
					@foreach($craftTypes as $craftType)
						<li><a href="{{ route('craft-types.show', $craftType) }}">{{ $craftType->name }}</a></li>
					@endforeach
				</ul>
			</div>

			<div class="row shedule">
				<div class="row--small">
					<h2>Расписание</h2>
					<div class="drivers">
						@forelse($masterClasses as $masterClass)
							<div class="driver grid">
								<div class="driver-left grid">
									<div class="driver-photo">
										<img src="{{ asset('img/driver1.png') }}">
									</div>
									<div class="driver-text">
										<div class="driver-name">{{ $masterClass->master?->name }}</div>
										<div class="driver-desc">
											<b>{{ \Illuminate\Support\Str::limit($masterClass->title, 120) }}</b><br>
											{{ \Illuminate\Support\Str::limit($masterClass->description, 500) }}<br>
											Стоимость: {{ $masterClass->price }} руб.<br>
											Свободных мест: {{ $masterClass->free_places }}
										</div>
									</div>
								</div>
								<div class="driver-right">
									@if($sessionUser && ($sessionUser['role'] ?? '') === 'visitor')
										@if($masterClass->is_expired)
											<div class="driver-note">Мастер-класс завершён</div>
										@elseif($masterClass->free_places < 1)
											<div class="driver-note">Свободных мест нет</div>
										@elseif($userBookings->contains('id', $masterClass->id))
											<div class="driver-note">Вы уже записаны</div>
											<a href="{{ route('booking.cancel', ['masterClass' => $masterClass, 'source' => 'category']) }}" class="booking-link booking-link--light">Отменить запись</a>
										@else
											<a href="{{ route('booking.confirm', $masterClass) }}"><button class="driver-btn">записаться</button></a>
										@endif
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
