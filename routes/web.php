<?php

use Illuminate\Support\Facades\Route;


Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::livewire('/recruitment', 'pages::recruitment')->name('recruitment')->middleware('auth');
Route::livewire('/category', 'pages::product.category')->name('category')->middleware('auth');
Route::livewire('/category/{category}/product', 'pages::product.product')->name('product')->middleware(['auth', 'signed']);
Route::livewire('/category/{category}/batch', 'pages::product.batch')->name('batch')->middleware(['auth', 'signed']);
Route::livewire('/customers', 'pages::customers')->name('customers')->middleware('auth');
Route::livewire('invoice', 'pages::invoice.invocies')->name('invoice')->middleware('auth');
Route::livewire('invoice/{invoice}/invoiceitems', 'pages::invoice.invoiceitems')->name('invoiceitems')->middleware(['auth', 'signed']);
Route::livewire('/sell', 'pages::sell.sell')->name('sell')->middleware('auth');




