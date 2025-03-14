<?php

use Livewire\Volt\Volt;

Volt::route('/home', 'users.index')->name('home');
Volt::route('/', 'users.index')->name('users.index');
Volt::route('/login', 'auth.login')->name('login');
Volt::route('/register', 'auth.register')->name('register');
Volt::route('/profile', 'settings.profile')->name('profile');
Volt::route('/settings', 'appearance')->name('settings');

Volt::route('/question-types', 'question-types.index')->name('question-type.index');
