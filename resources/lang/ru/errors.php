<?php

use App\Helpers\ResponseError;
use App\Models\Translation;

$e = new ResponseError;
$languages = Translation::translationList()->where('locale', 'ru')->pluck('value', 'key')->toArray();

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

    $e::NO_ERROR  => $languages['NO_ERROR']  ?? 'Успешно',
    $e::ERROR_100 => $languages['ERROR_100'] ?? 'Пользователь не авторизован.',
    $e::ERROR_101 => $languages['ERROR_101'] ?? 'У пользователя нет нужных ролей.',
    $e::ERROR_102 => $languages['ERROR_102'] ?? 'Логин или пароль неверный.',
    $e::ERROR_103 => $languages['ERROR_103'] ?? 'Адрес электронной почты пользователя не подтвержден.',
    $e::ERROR_104 => $languages['ERROR_104'] ?? 'Номер телефона пользователя не подтвержден.',
    $e::ERROR_105 => $languages['ERROR_105'] ?? 'Учетная запись пользователя не подтверждена.',
    $e::ERROR_106 => $languages['ERROR_106'] ?? 'Пользователь уже существует.',
    $e::ERROR_107 => $languages['ERROR_107'] ?? 'Пожалуйста, войдите, используя соц-сети.',
    $e::ERROR_108 => $languages['ERROR_108'] ?? 'У пользователя нет кошелька.',
    $e::ERROR_109 => $languages['ERROR_109'] ?? 'Недостаточно средств на кошельке.',
    $e::ERROR_110 => $languages['ERROR_110'] ?? 'Невозможно обновить эту роль пользователя.',
    $e::ERROR_111 => $languages['ERROR_111'] ?? 'Вы можете купить только :quantity продуктов.',
    $e::ERROR_112 => $languages['ERROR_112'] ?? 'когда статус: :verify вы должны добавить текст $verify_code в тело и альтернативное тело',
    $e::ERROR_113 => $languages['ERROR_113'] ?? 'У курьера нет эл. кошелька',
    $e::ERROR_114 => $languages['ERROR_114'] ?? 'У продавца нет эл. кошелька',
    $e::ERROR_115 => $languages['ERROR_115'] ?? 'Неправильный номер телефона',
    $e::ERROR_116 => $languages['ERROR_116'] ?? 'Вы уже активировали рекламу',
    $e::ERROR_117 => $languages['ERROR_117'] ?? 'Нужно указать телефон',
    $e::ERROR_118 => $languages['ERROR_118'] ?? 'Магазин закрыт',
    $e::ERROR_119 => $languages['ERROR_119'] ?? 'Дубликат опций',

    $e::ERROR_201 => $languages['ERROR_201'] ?? 'Неверный одноразовый пароль',
    $e::ERROR_202 => $languages['ERROR_202'] ?? 'Слишком много запросов, попробуйте позже',
    $e::ERROR_203 => $languages['ERROR_203'] ?? 'Срок действия OTP-кода истек',

    $e::ERROR_204 => $languages['ERROR_204'] ?? 'Вы еще не продавец или ваш магазин не создан',
    $e::ERROR_205 => $languages['ERROR_205'] ?? 'Магазин уже создан',
    $e::ERROR_206 => $languages['ERROR_206'] ?? 'У пользователя уже есть Магазин',
    $e::ERROR_207 => $languages['ERROR_207'] ?? 'Не могу обновить продавца магазина',
    $e::ERROR_208 => $languages['ERROR_208'] ?? 'Подписка уже активна',
    $e::ERROR_209 => $languages['ERROR_209'] ?? 'Зона доставки магазина уже создана',
    $e::ERROR_210 => $languages['ERROR_210'] ?? 'Доставка уже прикреплена',
    $e::ERROR_211 => $languages['ERROR_211'] ?? 'неверный доставщик или токен не найден',
    $e::ERROR_212 => $languages['ERROR_212'] ?? 'Не ваш магазин. Проверьте свой другой аккаунт',
    $e::ERROR_213 => $languages['ERROR_213'] ?? 'Главный Шоп.',
    $e::ERROR_214 => $languages['ERROR_214'] ?? 'Вы не продавец',
    $e::ERROR_215 => $languages['ERROR_215'] ?? 'Неверный код или срок действия токена истек',
    $e::ERROR_216 => $languages['ERROR_216'] ?? 'Подтвердить код отправить',
    $e::ERROR_217 => $languages['ERROR_217'] ?? 'Пользователь отправляет электронное письмо',
    $e::ERROR_218 => $languages['ERROR_218'] ?? 'Не активирован',
    $e::ERROR_219 => $languages['ERROR_219'] ?? 'Срок действия вашей подписки истек.',
    $e::ERROR_220 => $languages['ERROR_220'] ?? 'Срок действия вашего лимита продуктов по подписке истек',

    $e::ERROR_249 => $languages['ERROR_249'] ?? 'Недействительный купон',
    $e::ERROR_250 => $languages['ERROR_250'] ?? 'Срок действия купона истек',
    $e::ERROR_251 => $languages['ERROR_251'] ?? 'Купон уже использован',
    $e::ERROR_252 => $languages['ERROR_252'] ?? 'Статус уже использован',
    $e::ERROR_253 => $languages['ERROR_253'] ?? 'Неверный тип статуса',
    $e::ERROR_254 => $languages['ERROR_254'] ?? 'Не удается обновить статус отмены',
    $e::ERROR_255 => $languages['ERROR_255'] ?? 'Невозможно обновить статус заказа, если заказ уже в пути или доставлен',

    $e::ERROR_400 => $languages['ERROR_400'] ?? 'Плохой запрос.',
    $e::ERROR_401 => $languages['ERROR_401'] ?? 'Неавторизованный.',
    $e::ERROR_403 => $languages['ERROR_403'] ?? 'Ваш проект не активирован.',
    $e::ERROR_404 => $languages['ERROR_404'] ?? 'Товар не найден.',
    $e::ERROR_415 => $languages['ERROR_415'] ?? 'Нет связи с базой данных',
    $e::ERROR_422 => $languages['ERROR_422'] ?? 'Ошибка проверки',
    $e::ERROR_429 => $languages['ERROR_429'] ?? 'Слишком много запросов',
    $e::ERROR_430 => $languages['ERROR_430'] ?? 'Количество на складе 0',
    $e::ERROR_431 => $languages['ERROR_431'] ?? 'Активная валюта по умолчанию не найдена',
    $e::ERROR_432 => $languages['ERROR_430'] ?? 'Неопределенный тип',
    $e::ERROR_434 => $languages['ERROR_432'] ?? 'Тип оплаты должен быть кошелёк или наличные',
    $e::ERROR_435 => $languages['ERROR_434'] ?? 'Магазин закрыт',
    $e::ERROR_436 => $languages['ERROR_435'] ?? ':shop не доставляет в ваш адрес. Удалите магазин из корзины или выберите другой адрес',

    $e::ERROR_501 => $languages['ERROR_501'] ?? 'Ошибка при создании',
    $e::ERROR_502 => $languages['ERROR_502'] ?? 'Ошибка при обновлении',
    $e::ERROR_503 => $languages['ERROR_503'] ?? 'Ошибка при удалении.',
    $e::ERROR_504 => $languages['ERROR_504'] ?? 'Невозможно удалить запись со значениями.',
    $e::ERROR_505 => $languages['ERROR_505'] ?? 'Невозможно удалить запись по умолчанию. :ids',
    $e::ERROR_506 => $languages['ERROR_506'] ?? 'Уже существует.',
    $e::ERROR_507 => $languages['ERROR_507'] ?? 'Невозможно удалить запись с продуктами.',
    $e::ERROR_508 => $languages['ERROR_508'] ?? 'Неверный формат Excel или неверные данные.',
    $e::ERROR_509 => $languages['ERROR_509'] ?? 'Неверный формат даты.',
    $e::ERROR_510 => $languages['ERROR_510'] ?? 'Адрес правильный.',

    $e::CONFIRMATION_CODE               => $languages['CONFIRMATION_CODE'] ?? 'Код подтверждения :code',
    $e::NEW_ORDER                       => $languages['NEW_ORDER'] ?? 'Новый заказ для тебя # :id',
    $e::NEW_PARCEL_ORDER                => $languages['NEW_PARCEL_ORDER'] ?? 'Новая почта для тебя # :id',
    $e::PHONE_OR_EMAIL_NOT_FOUND        => $languages['PHONE_OR_EMAIL_NOT_FOUND'] ?? 'Телефон или электронная почта не найдена',
    $e::ORDER_NOT_FOUND                 => $languages['ORDER_NOT_FOUND'] ?? 'Заказ не найден',
    $e::ORDER_REFUNDED                  => $languages['ORDER_REFUNDED'] ?? 'Заказ возвращен',
    $e::ORDER_POINT                     => $languages['ORDER_POINT'] ?? 'Заказ самовывоз',
    $e::SHOP_NOT_FOUND                  => $languages['SHOP_NOT_FOUND'] ?? 'Магазин не найден',
    $e::OTHER_SHOP                      => $languages['OTHER_SHOP'] ?? 'Другой магазин',
    $e::NOT_IN_POLYGON                  => $languages['NOT_IN_POLYGON'] ?? 'Не в полигоне',
    $e::CURRENCY_NOT_FOUND              => $languages['CURRENCY_NOT_FOUND'] ?? 'Валюта не найдена',
    $e::LANGUAGE_NOT_FOUND              => $languages['LANGUAGE_NOT_FOUND'] ?? 'Язык не найден',
    $e::CANT_DELETE_ORDERS              => $languages['CANT_DELETE_ORDERS'] ?? 'Не могу удалить заказы :ids',
    $e::CANT_UPDATE_ORDERS              => $languages['CANT_UPDATE_ORDERS'] ?? 'Не могу обновить заказы :ids',
    $e::ADD_CASHBACK                    => $languages['ADD_CASHBACK'] ?? 'Добавлен кэшбек',
    $e::WALLET_TOP_UP                   => $languages['WALLET_TOP_UP'] ?? ':sender пополнил ваш кошелёк',
    $e::WALLET_WITHDRAW                 => $languages['WALLET_WITHDRAW'] ?? ':sender снял с вашего кошелька',
    $e::STATUS_CHANGED                  => $languages['STATUS_CHANGED'] ?? 'Статус вашего заказа #:id изменен на :status',
    $e::SHOP_STATUS_CHANGED             => $languages['SHOP_STATUS_CHANGED'] ?? 'Статус вашего магазина изменен на :status',
    $e::PAYOUT_ACCEPTED                 => $languages['PAYOUT_ACCEPTED'] ?? 'Выплата уже :status',
    $e::CANT_DELETE_IDS                 => $languages['CANT_DELETE_IDS'] ?? 'Не могу удалить :ids',
    $e::USER_NOT_FOUND                  => $languages['USER_NOT_FOUND'] ?? 'Пользователь не найден',
    $e::USER_IS_BANNED                  => $languages['USER_IS_BANNED'] ?? 'Пользователь забанен!',
    $e::INCORRECT_LOGIN_PROVIDER        => $languages['INCORRECT_LOGIN_PROVIDER'] ?? 'Пожалуйста, войдите, используя facebook или google.',
    $e::FIN_FO                          => $languages['FIN_FO'] ?? 'Вам нужно расширение информации о файле php',
    $e::USER_SUCCESSFULLY_REGISTERED    => $languages['USER_SUCCESSFULLY_REGISTERED'] ?? 'Пользователь успешно зарегистрирован',
    $e::USER_CARTS_IS_EMPTY             => $languages['USER_CARTS_IS_EMPTY'] ?? 'Корзины пользователей пусты',
    $e::PRODUCTS_IS_EMPTY               => $languages['PRODUCTS_IS_EMPTY'] ?? 'Товары пусты',
    $e::RECORD_WAS_SUCCESSFULLY_CREATED => $languages['RECORD_WAS_SUCCESSFULLY_CREATED'] ?? 'Запись успешно создана',
    $e::RECORD_WAS_SUCCESSFULLY_UPDATED => $languages['RECORD_WAS_SUCCESSFULLY_UPDATED'] ?? 'Запись успешно обновлена',
    $e::RECORD_WAS_SUCCESSFULLY_DELETED => $languages['RECORD_WAS_SUCCESSFULLY_DELETED'] ?? 'Запись успешно удалена',
    $e::IMAGE_SUCCESSFULLY_UPLOADED     => $languages['IMAGE_SUCCESSFULLY_UPLOADED'] ?? 'Успех :title, :type',
    $e::EMPTY_STATUS                    => $languages['EMPTY_STATUS'] ?? 'Статус пуст',
    $e::SUCCESS                         => $languages['SUCCESS'] ?? 'Успех',
    $e::DELIVERYMAN_IS_NOT_CHANGED      => $languages['DELIVERYMAN_IS_NOT_CHANGED'] ?? 'Вам нужно сменить курьера',
    $e::CATEGORY_IS_PARENT              => $languages['CATEGORY_IS_PARENT'] ?? 'Категория является родительской',
    $e::TYPE_PRICE_USER                 => $languages['TYPE_PRICE_USER'] ?? 'Тип, цена или пользователь пусты',
    $e::NOTHING_TO_UPDATE               => $languages['NOTHING_TO_UPDATE'] ?? 'Нечего обновлять',
    $e::EMPTY                           => $languages['EMPTY'] ?? 'Не указано',
    $e::NOT_IN_PARCEL_POLYGON           => $languages['NOT_IN_PARCEL_POLYGON'] ?? 'Наш сервис не работает на этом расстоянии, выберите другой тип или адрес. Лимит :km км',
    $e::CANT_UPDATE_EMPTY_ORDER         => $languages['CANT_UPDATE_EMPTY_ORDER'] ?? 'Нельзя создать или обновить пустой заказ',
    $e::ORDER_OR_DELIVERYMAN_IS_EMPTY   => $languages['ORDER_OR_DELIVERYMAN_IS_EMPTY'] ?? 'Заказ не найден или курьер не прикреплен',
    $e::DELIVERYMAN_SETTING_EMPTY       => $languages['DELIVERYMAN_SETTING_EMPTY'] ?? 'У вас не заданы настройки',
];
