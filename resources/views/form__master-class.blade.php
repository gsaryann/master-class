<!DOCTYPE html>
<html>
<head>
	<title>{{ $isEdit ? 'Редактирование мастер-класса' : 'Добавление мастер-класса' }} | ОчУмелые ручки</title>
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
				<a href="{{ route('cabinet') }}">Назад</a>
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
				<form method="post" action="{{ $isEdit ? route('master-classes.update', $masterClass) : route('master-classes.store') }}">
					@csrf
					@if($isEdit)
						@method('put')
					@endif
					<h2>{{ $isEdit ? 'Форма редактирования мастер-класса' : 'Форма добавления мастер-класса' }}</h2>
					@if($isEdit)
						<p class="form-note">
							@if(($participantsCount ?? 0) > 0)
								Записано: {{ $participantsCount }} чел.
							@else
								Записей пока нет.
							@endif
						</p>
					@endif
					<div class="form-group">
						<label>Вид творчества</label>
						@if($isEdit && ($participantsCount ?? 0) > 0)
							<input type="hidden" name="craft_type_id" value="{{ $masterClass->craft_type_id }}">
						@endif
						<select name="craft_type_id" class="@error('craft_type_id') field-error @enderror" @disabled($isEdit && ($participantsCount ?? 0) > 0)>
							<option value="">Выберите вид творчества</option>
							@foreach($craftTypes as $craftType)
								<option value="{{ $craftType->id }}" @selected(old('craft_type_id', $masterClass->craft_type_id) == $craftType->id)>{{ $craftType->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label>Название мастер-класса</label>
						<input type="text" name="title" maxlength="{{ \App\Models\MasterClass::TITLE_MAX_LENGTH }}" value="{{ old('title', $masterClass->title) }}" class="@error('title') field-error @enderror">
					</div>
					<div class="form-group">
						<label>Описание мастер-класса</label>
						<textarea name="description" maxlength="{{ \App\Models\MasterClass::DESCRIPTION_MAX_LENGTH }}" class="@error('description') field-error @enderror">{{ old('description', $masterClass->description) }}</textarea>
					</div>
					<div class="form-group">
						<label>Дата</label>
						<input id="scheduled_date" type="date" name="scheduled_date" value="{{ old('scheduled_date', optional($masterClass->scheduled_date)->format('Y-m-d')) }}" class="@error('scheduled_date') field-error @enderror">
					</div>
					<div class="form-group">
						<label>Время</label>
						<select id="start_time" name="start_time" class="@error('start_time') field-error @enderror">
							<option value="">Выберите время</option>
							@foreach($timeSlots as $slotValue => $slotLabel)
								<option value="{{ $slotValue }}" @selected(old('start_time', $masterClass->start_time) === $slotValue)>{{ $slotLabel }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label>Количество человек в группе</label>
						<input type="number" name="max_people" min="{{ max(1, $participantsCount ?? 0) }}" value="{{ old('max_people', $masterClass->max_people) }}" class="@error('max_people') field-error @enderror">
					</div>
					<div class="form-group">
						<label>Стоимость</label>
						@if($isEdit && ($participantsCount ?? 0) > 0)
							<input type="hidden" name="price" value="{{ $masterClass->price }}">
							<input type="number" value="{{ $masterClass->price }}" disabled class="field-disabled">
						@else
							<input type="number" name="price" value="{{ old('price', $masterClass->price) }}" class="@error('price') field-error @enderror">
						@endif
					</div>
					<div class="form-group">
						<button class="btn">{{ $isEdit ? 'Сохранить изменения' : 'Отправить' }}</button>
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

		const dateInput = document.getElementById('scheduled_date');
		const timeSelect = document.getElementById('start_time');
		const ignoredMasterClassId = @json($isEdit ? $masterClass->id : null);

		async function refreshSlots() {
			const currentValue = timeSelect.value;
			const now = new Date();
			const today = now.toISOString().slice(0, 10);
			const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

			[...timeSelect.options].forEach((option) => {
				if (option.value === '') {
					option.disabled = false;
					return;
				}

				option.disabled = false;
			});

			if (!dateInput.value) {
				return;
			}

			const params = new URLSearchParams({ date: dateInput.value });
			if (ignoredMasterClassId) {
				params.append('ignore_id', ignoredMasterClassId);
			}

			const response = await fetch(`{{ route('master-classes.occupied-slots') }}?${params.toString()}`);
			const occupiedSlots = await response.json();

			[...timeSelect.options].forEach((option) => {
				if (option.value === '') {
					return;
				}

				const isPastToday = dateInput.value === today && option.value <= currentTime;
				option.disabled = occupiedSlots.includes(option.value) || isPastToday;
			});

			if (currentValue && occupiedSlots.includes(currentValue)) {
				timeSelect.value = '';
			}
		}

		dateInput.addEventListener('change', refreshSlots);
		refreshSlots();
	</script>
</body>
</html>
