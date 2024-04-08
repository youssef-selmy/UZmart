<?php

use App\Helpers\ResponseError;
use App\Models\Translation;

$e = new ResponseError;
$languages = Translation::translationList()->where('locale', 'en')->pluck('value', 'key')->toArray();

return [

    /*
    |--------------------------------------------------------------------------
    | Pagination Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the paginator library to build
    | the simple pagination links. You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    $e::NO_ERROR  => $languages['NO_ERROR']  ?? 'Successfully',
    $e::ERROR_100 => $languages['ERROR_100'] ?? 'User is not logged in.',
    $e::ERROR_101 => $languages['ERROR_101'] ?? 'User does not have the right roles.',
    $e::ERROR_102 => $languages['ERROR_102'] ?? 'Login or password is incorrect.',
    $e::ERROR_103 => $languages['ERROR_103'] ?? 'User email address is not verified.',
    $e::ERROR_104 => $languages['ERROR_104'] ?? 'User phone number is not verified.',
    $e::ERROR_105 => $languages['ERROR_105'] ?? 'User account is not verified.',
    $e::ERROR_106 => $languages['ERROR_106'] ?? 'User already exists.',
    $e::ERROR_107 => $languages['ERROR_107'] ?? 'Please login using socials.',
    $e::ERROR_108 => $languages['ERROR_108'] ?? 'The user does not have a Wallet.',
    $e::ERROR_109 => $languages['ERROR_109'] ?? 'Insufficient wallet balance.',
    $e::ERROR_110 => $languages['ERROR_110'] ?? 'Can\'t update this user role.',
    $e::ERROR_111 => $languages['ERROR_111'] ?? 'You can buy only :quantity products.',
    $e::ERROR_112 => $languages['ERROR_112'] ?? 'when status: :verify you should add text $verify_code on body and alt body',
    $e::ERROR_113 => $languages['ERROR_113'] ?? 'Deliveryman doesn\'t have Wallet',
    $e::ERROR_114 => $languages['ERROR_114'] ?? 'Seller doesn\'t have Wallet',
    $e::ERROR_115 => $languages['ERROR_115'] ?? 'Phone not found',
    $e::ERROR_116 => $languages['ERROR_116'] ?? 'Ads already activated',
    $e::ERROR_117 => $languages['ERROR_117'] ?? 'Phone is required',
    $e::ERROR_118 => $languages['ERROR_118'] ?? 'Shop is closed',
    $e::ERROR_119 => $languages['ERROR_119'] ?? 'Duplicate stock',

    $e::ERROR_201 => $languages['ERROR_201'] ?? 'Wrong OTP Code',
    $e::ERROR_202 => $languages['ERROR_202'] ?? 'Too many request, try later',
    $e::ERROR_203 => $languages['ERROR_203'] ?? 'OTP code is expired',

    $e::ERROR_204 => $languages['ERROR_204'] ?? 'You are not seller yet or your shop is not created',
    $e::ERROR_205 => $languages['ERROR_205'] ?? 'Shop already created',
    $e::ERROR_206 => $languages['ERROR_206'] ?? 'User already has Shop',
    $e::ERROR_207 => $languages['ERROR_207'] ?? 'Can\'t update Shop Seller',
    $e::ERROR_208 => $languages['ERROR_208'] ?? 'Subscription already active',
    $e::ERROR_209 => $languages['ERROR_209'] ?? 'Shop delivery zone already created',
    $e::ERROR_210 => $languages['ERROR_210'] ?? 'Delivery already attached',
    $e::ERROR_211 => $languages['ERROR_211'] ?? 'invalid deliveryman or token not found',
    $e::ERROR_212 => $languages['ERROR_212'] ?? 'Not your shop. Check your other account',
    $e::ERROR_213 => $languages['ERROR_213'] ?? 'Shop is parent',
    $e::ERROR_214 => $languages['ERROR_214'] ?? 'You are not shop',
    $e::ERROR_215 => $languages['ERROR_215'] ?? 'Incorrect code or token expired',
    $e::ERROR_216 => $languages['ERROR_216'] ?? 'Verify code send',
    $e::ERROR_217 => $languages['ERROR_217'] ?? 'User send email',
    $e::ERROR_218 => $languages['ERROR_218'] ?? 'not activated',
    $e::ERROR_219 => $languages['ERROR_219'] ?? 'Your subscription is expired at',
    $e::ERROR_220 => $languages['ERROR_220'] ?? 'Your subscription product limit has expired',

    $e::ERROR_249 => $languages['ERROR_249'] ?? 'Invalid Coupon',
    $e::ERROR_250 => $languages['ERROR_250'] ?? 'Coupon expired',
    $e::ERROR_251 => $languages['ERROR_251'] ?? 'Coupon already used',
    $e::ERROR_252 => $languages['ERROR_252'] ?? 'Status already used',
    $e::ERROR_253 => $languages['ERROR_253'] ?? 'Wrong status type',
    $e::ERROR_254 => $languages['ERROR_254'] ?? 'Can\'t update Cancel status',
    $e::ERROR_255 => $languages['ERROR_255'] ?? 'Can\'t update Order status if order already on a way or delivered',

    $e::ERROR_400 => $languages['ERROR_400'] ?? 'Bad request.',
    $e::ERROR_401 => $languages['ERROR_401'] ?? 'Unauthorized.',
    $e::ERROR_403 => $languages['ERROR_403'] ?? 'Your project is not activated.',
    $e::ERROR_404 => $languages['ERROR_404'] ?? 'Item\'s not found.',
    $e::ERROR_415 => $languages['ERROR_415'] ?? 'No connection to database',
    $e::ERROR_422 => $languages['ERROR_422'] ?? 'Validation Error',
    $e::ERROR_429 => $languages['ERROR_429'] ?? 'Too many requests',
    $e::ERROR_430 => $languages['ERROR_430'] ?? 'Product quantity 0',
    $e::ERROR_431 => $languages['ERROR_431'] ?? 'Active default currency not found',
    $e::ERROR_430 => $languages['ERROR_430'] ?? 'Product quantity 0',
    $e::ERROR_432 => $languages['ERROR_432'] ?? 'Undefined Type',
    $e::ERROR_434 => $languages['ERROR_434'] ?? 'Payment type must be only wallet or cash',
    $e::ERROR_435 => $languages['ERROR_435'] ?? 'Shop is closed',
    $e::ERROR_436 => $languages['ERROR_436'] ?? ':shop does not deliver to your address. Remove the store from your cart or select a different address',

    $e::ERROR_501 => $languages['ERROR_501'] ?? 'Error during creating',
    $e::ERROR_502 => $languages['ERROR_502'] ?? 'Error during updating',
    $e::ERROR_503 => $languages['ERROR_503'] ?? 'Error during deleting.',
    $e::ERROR_504 => $languages['ERROR_504'] ?? 'Can\'t delete record that has values.',
    $e::ERROR_505 => $languages['ERROR_505'] ?? 'Can\'t delete default record. # :ids',
    $e::ERROR_506 => $languages['ERROR_506'] ?? 'Already exists.',
    $e::ERROR_507 => $languages['ERROR_507'] ?? 'Can\'t delete record that has products.',
    $e::ERROR_508 => $languages['ERROR_508'] ?? 'Excel format incorrect or data invalid.',
    $e::ERROR_509 => $languages['ERROR_509'] ?? 'Invalid date format.',
    $e::ERROR_510 => $languages['ERROR_510'] ?? 'Address in correct.',

    $e::CONFIRMATION_CODE               => $languages['CONFIRMATION_CODE'] ?? 'Confirmation code :code',
    $e::NEW_ORDER                       => $languages['NEW_ORDER'] ?? 'New order for you # :id',
    $e::NEW_PARCEL_ORDER                => $languages['NEW_PARCEL_ORDER'] ?? 'New parcel order for you # :id',
    $e::PHONE_OR_EMAIL_NOT_FOUND        => $languages['PHONE_OR_EMAIL_NOT_FOUND'] ?? 'Phone or Email not found',
    $e::ORDER_NOT_FOUND                 => $languages['ORDER_NOT_FOUND'] ?? 'Order not found',
    $e::ORDER_REFUNDED                  => $languages['ORDER_REFUNDED'] ?? 'Order refunded',
    $e::ORDER_POINT                     => $languages['ORDER_POINT'] ?? 'Order is point',
    $e::SHOP_NOT_FOUND                  => $languages['SHOP_NOT_FOUND'] ?? 'Shop not found',
    $e::OTHER_SHOP                      => $languages['OTHER_SHOP'] ?? 'Other shop',
    $e::NOT_IN_POLYGON                  => $languages['NOT_IN_POLYGON'] ?? 'Not in polygon',
    $e::CURRENCY_NOT_FOUND              => $languages['CURRENCY_NOT_FOUND'] ?? 'Currency not found',
    $e::LANGUAGE_NOT_FOUND              => $languages['LANGUAGE_NOT_FOUND'] ?? 'Language not found',
    $e::CANT_DELETE_ORDERS              => $languages['CANT_DELETE_ORDERS'] ?? 'Can`t delete orders :ids',
    $e::CANT_UPDATE_ORDERS              => $languages['CANT_UPDATE_ORDERS'] ?? 'Can`t update orders :ids',
    $e::ADD_CASHBACK                    => $languages['ADD_CASHBACK'] ?? 'Added cashback',
    $e::WALLET_TOP_UP                   => $languages['WALLET_TOP_UP'] ?? ':sender top up your wallet',
    $e::WALLET_WITHDRAW                 => $languages['WALLET_WITHDRAW'] ?? ':sender withdraw your wallet',
    $e::STATUS_CHANGED                  => $languages['STATUS_CHANGED'] ?? 'Your order #:id status has been changed to :status',
    $e::SHOP_STATUS_CHANGED             => $languages['SHOP_STATUS_CHANGED'] ?? 'Your shop status has been changed to :status',
    $e::PAYOUT_ACCEPTED                 => $languages['PAYOUT_ACCEPTED'] ?? 'Payout already :status',
    $e::CANT_DELETE_IDS                 => $languages['CANT_DELETE_IDS'] ?? 'Can`t delete :ids',
    $e::USER_NOT_FOUND                  => $languages['USER_NOT_FOUND'] ?? 'User not found',
    $e::USER_IS_BANNED                  => $languages['USER_IS_BANNED'] ?? 'User is banned!',
    $e::INCORRECT_LOGIN_PROVIDER        => $languages['INCORRECT_LOGIN_PROVIDER'] ?? 'Please login using facebook or google.',
    $e::FIN_FO                          => $languages['FIN_FO'] ?? 'You need on php file info extension',
    $e::USER_SUCCESSFULLY_REGISTERED    => $languages['USER_SUCCESSFULLY_REGISTERED'] ?? 'User successfully registered',
    $e::USER_CARTS_IS_EMPTY             => $languages['USER_CARTS_IS_EMPTY'] ?? 'User carts is empty',
    $e::PRODUCTS_IS_EMPTY               => $languages['PRODUCTS_IS_EMPTY'] ?? 'Products is empty',
    $e::RECORD_WAS_SUCCESSFULLY_CREATED => $languages['RECORD_WAS_SUCCESSFULLY_CREATED'] ?? 'Record was successfully created',
    $e::RECORD_WAS_SUCCESSFULLY_UPDATED => $languages['RECORD_WAS_SUCCESSFULLY_UPDATED'] ?? 'Record was successfully updated',
    $e::RECORD_WAS_SUCCESSFULLY_DELETED => $languages['RECORD_WAS_SUCCESSFULLY_DELETED'] ?? 'Record was successfully deleted',
    $e::IMAGE_SUCCESSFULLY_UPLOADED     => $languages['IMAGE_SUCCESSFULLY_UPLOADED'] ?? 'Success :title, :type',
    $e::EMPTY_STATUS                    => $languages['EMPTY_STATUS'] ?? 'Status is empty',
    $e::SUCCESS                         => $languages['SUCCESS'] ?? 'Success',
    $e::DELIVERYMAN_IS_NOT_CHANGED      => $languages['DELIVERYMAN_IS_NOT_CHANGED'] ?? 'You need change delivery man',
    $e::CATEGORY_IS_PARENT              => $languages['CATEGORY_IS_PARENT'] ?? 'Category is parent',
    $e::TYPE_PRICE_USER                 => $languages['TYPE_PRICE_USER'] ?? 'Type, price or user is empty',
    $e::NOTHING_TO_UPDATE               => $languages['NOTHING_TO_UPDATE'] ?? 'Nothing to update',
    $e::EMPTY                           => $languages['EMPTY'] ?? 'Empty',
    $e::NOT_IN_PARCEL_POLYGON           => $languages['NOT_IN_PARCEL_POLYGON'] ?? 'Our service does not work at this distance, please choose another type or another address. Limit :km km',
    $e::CANT_UPDATE_EMPTY_ORDER         => $languages['CANT_UPDATE_EMPTY_ORDER'] ?? 'Cant create or update empty order',
    $e::ORDER_OR_DELIVERYMAN_IS_EMPTY   => $languages['ORDER_OR_DELIVERYMAN_IS_EMPTY'] ?? 'Order not found or deliveryman is not attached',
    $e::DELIVERYMAN_SETTING_EMPTY       => $languages['DELIVERYMAN_SETTING_EMPTY'] ?? 'Your setting is empty',
];
