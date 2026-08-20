<?php

use Illuminate\Support\Facades\Route;


Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('PersonnelManagement_s_a', 'super_admin_dashboard.PersonnelManagement')->name('PersonnelManagement_s_a');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('ProductHandel_s_a', 'super_admin_dashboard.ProductHandel')->name('ProductHandel');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('customer_s_a', 'super_admin_dashboard.customer_icon')->name('customer_s_a');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('sale_s_a', 'super_admin_dashboard.sale_icon')->name('sale_s_a');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('invoice_s_a', 'super_admin_dashboard.invoice_icon')->name('invoice_s_a');
});





Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('salary_s_a', 'super_admin_dashboard.salary_icon')->name('salary_s_a');
});





Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('my_salary', 'super_admin_dashboard.my_salary')->name('my_salary');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('year_s_a', 'super_admin_dashboard.year')->name('year_s_a');
});


require __DIR__.'/settings.php';

Route::livewire('/recruitment', 'pages::recruitment')->name('recruitment')->middleware(['auth', 'signed']);
Route::livewire('/profile', 'pages::profile')->name('profile')->middleware(['auth', 'signed']);
Route::livewire('/category', 'pages::product.category')->name('category')->middleware(['auth', 'signed']);
Route::livewire('/product/{category}/product', 'pages::product.product')->name('product')->middleware(['auth', 'signed']);
Route::livewire('/Product/{category}/batch', 'pages::product.batch')->name('batch')->middleware(['auth', 'signed']);
Route::livewire('/customers', 'pages::customers')->name('customers')->middleware('auth');
Route::livewire('invoice', 'pages::invoice.invocies')->name('invoice')->middleware(['auth', 'signed']);
Route::livewire('invoice/{invoice}/invoiceitems', 'pages::invoice.invoiceitems')->name('invoiceitems')->middleware(['auth', 'signed']);
Route::livewire('/sell', 'pages::sell.sell')->name('sell')->middleware(['auth', 'signed']);
Route::livewire('/select', 'pages::auth.select_role')
    ->name('select')
    ->middleware(['auth', 'verified']);


Route::livewire('year', 'pages::salary.year')->name('year')->middleware('auth');
Route::livewire('/month/{year}/month', 'pages::salary.month')->name('month')->middleware(['auth', 'signed']);
Route::livewire('/week/{year}/week', 'pages::salary.week')->name('week')->middleware(['auth', 'signed']);
Route::livewire('salary', 'pages::salary.salary')->name('salary')->middleware('auth');
Route::livewire('paid', 'pages::salary.paid')->name('paid')->middleware('auth');







