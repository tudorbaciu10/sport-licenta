<?php

// Only the rules Sport.md uses. Anything missing falls back to the English file from Laravel.
return array (
  'after' => 'Поле «:attribute» должно быть позже :date.',
  'array' => 'Поле «:attribute» должно быть списком.',
  'boolean' => 'Поле «:attribute» должно быть да или нет.',
  'confirmed' => 'Подтверждение поля «:attribute» не совпадает.',
  'date' => 'Поле «:attribute» не является датой.',
  'date_format' => 'Поле «:attribute» должно быть в формате :format.',
  'email' => 'Введите корректный адрес, например ion@exemplu.md.',
  'exists' => 'Выбранное значение поля «:attribute» не существует.',
  'in' => 'Выбранное значение поля «:attribute» недопустимо.',
  'integer' => 'Поле «:attribute» должно быть целым числом.',
  'max' => 
  array (
    'array' => 'В поле «:attribute» не больше :max элементов.',
    'numeric' => 'Поле «:attribute» не может быть больше :max.',
    'string' => 'Поле «:attribute» не длиннее :max символов.',
  ),
  'min' => 
  array (
    'numeric' => 'Поле «:attribute» должно быть не меньше :min.',
    'string' => 'Поле «:attribute» должно быть не короче :min символов.',
  ),
  'password' => 
  array (
    'min' => 'Пароль должен быть не короче :min символов.',
  ),
  'required' => 'Заполните поле «:attribute».',
  'required_unless' => 'Выберите «:attribute».',
  'string' => 'Поле «:attribute» должно быть текстом.',
  'unique' => 'Аккаунт с таким значением поля «:attribute» уже существует.',
  'custom' => 
  array (
    'match_date_time' => 
    array (
      'after' => 'Матч должен быть в будущем.',
    ),
  ),
  'attributes' => 
  array (
    'sport_id' => 'спорт',
    'city_id' => 'город',
    'title' => 'название',
    'description' => 'заметка',
    'location_name' => 'место',
    'venue_type' => 'тип площадки',
    'match_date_time' => 'дата и время',
    'max_players' => 'количество игроков',
    'price' => 'цена',
    'price_collector' => 'кто собирает деньги',
    'equipment_by' => 'инвентарь',
    'rules' => 'правила',
    'rules.*' => 'правило',
    'name' => 'имя',
    'email' => 'email',
    'password' => 'пароль',
    'date' => 'день',
    'time' => 'время',
    'q' => 'поиск',
    'month' => 'месяц',
    'city' => 'город',
    'mine' => 'фильтр «мои»',
  ),
);
