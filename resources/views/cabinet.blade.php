<!DOCTYPE html>
<html>
<head>
	<title>{{ $isMasterCabinet ? 'Личный кабинет ведущего' : 'Личный кабинет пользователя' }} | ОчУмелые ручки</title>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/styles.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/responsive.css') }}">
</head>
<body class="dp">
	<div class="header">
		<div class="row grid middle between">
			<div class="logo">
				<a href="{{ route('home') }}"><img src="{{ asset('img/logo.png') }}"></a>
			</div>
			<div class="title">
				Клуб любителей творчества «ОчУмелые ручки»
			</div>
			<div class="auth">
				<a href="{{ route('home') }}">Главная</a> |
				<a href="{{ route('logout') }}">Выход</a>
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
			<div class="title"></div>
			<div class="row--small grid between">
				<div class="content driver-page">
					<div class="driver-page-photo">
						<img src="{{ asset($user->photo ?: 'img/driver-page.png') }}">
					</div>	
					<div class="driver-page-name">{{ $user->name }}</div>
					<div class="driver-page-text">
						<div class="driver-page-my">{{ $isMasterCabinet ? 'Мои мастер-классы' : 'Мои записи' }}</div>
						<table class="driver-page-table">
							<tbody>
								@if($isMasterCabinet)
									@forelse($user->masterClasses as $masterClass)
										<tr>
											<td>{{ $masterClass->scheduled_date->format('d.m.Y') }} {{ $masterClass->start_time }}</td>
											<td>
												<b>{{ \Illuminate\Support\Str::limit($masterClass->title, 120) }}</b><br>
												{{ \Illuminate\Support\Str::limit($masterClass->description, 500) }}<br>
												Стоимость: {{ $masterClass->price }} руб.<br>
												<a href="{{ route('master-classes.edit', $masterClass) }}">Редактировать</a>
												@foreach($masterClass->participants as $index => $participant)
													<p>
														{{ $index + 1 }}. {{ $participant->name }}<br>
														email: {{ $participant->email }} <br>
														tel: {{ $participant->phone }}
													</p>
												@endforeach
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="2">Пока мастер-классов нет.</td>
										</tr>
									@endforelse
								@else
									@forelse($user->bookedMasterClasses as $masterClass)
										<tr>
											<td>{{ $masterClass->scheduled_date->format('d.m.Y') }} {{ $masterClass->time_label }}</td>
											<td>
												<b>{{ \Illuminate\Support\Str::limit($masterClass->title, 120) }}</b><br>
												{{ $masterClass->craftType->name }}<br>
												Ведущий: {{ $masterClass->master?->name }}<br>
												Стоимость: {{ $masterClass->price }} руб.<br>
												<a href="{{ route('booking.cancel', ['masterClass' => $masterClass, 'source' => 'category']) }}">Отменить запись</a>
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="2">У вас пока нет записей.</td>
										</tr>
									@endforelse
								@endif
							</tbody>
						</table>
					</div>
					@if($isMasterCabinet)
						<div class="driver-page-btn-wrapper">
							<div class="driver-page-btn btn">
								<a href="{{ route('master-classes.create') }}">Добавить мастер-класс</a>
							</div>
						</div>
					@endif
				</div>
				<ul class="menu">
					@foreach($craftTypes as $craftType)
						<li><a href="{{ route('craft-types.show', $craftType) }}">{{ $craftType->name }}</a></li>
					@endforeach
				</ul>
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
