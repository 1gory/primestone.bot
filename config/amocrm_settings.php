<?php

const SUBDOMAIN = 'homeakril';
const REDIRECT_URI = 'https://primestone-bot.pr-stone.ru/';
const CLIENT_SECRET = 'R51CPHZRICWoYjlX9A0fC04A44OHwJtwrJDtTGwtg2cDRci0E6AKBfgL9cj8SfQ0'; // секретный ключ
const CLIENT_ID = 'a2b12821-ff2c-4085-81bd-eee46e4e75a6'; // id интеграции
const AUTHORIZATION_CODE = 'def50200fa72a8643ff0d674123b9ec33bcec1fe97bc5e71e2e775e933519844beb3f747849a2705bf763d213f3ccb4f0d19ac31e66f1a742f999819e1e3f3f82eb8ce79ed44ebd1feb843920c8d475795e62c0c5c4291e49def169d5ef927eb397f81081c6f7cd67fca485bbde6cd2110d1a309ec3c007161891ea772bbf92edfdf04176fff2fcd352355f2983a1208b2c52a0e115e9dac15def62a7e8a48b2c6a0e59b9b115687cb9bf6dab2a0da2605ed89d77b57cbff6b0c9189882195f50a4f188751c3b7520c80d255d66a06d5f1a0d699759936769cb70c73d58c93765229d2587e9da51d59d87eb8e29b28a2e7a4f8c45890c5dc61f0b3c54924c834cacc7ae3e21e98e4b9c9ac24f11d462f6cfa1d97dc29a2c71db42a199b19e7a0b9e6f406f68defdb055c6e70e98978b1d1087182a5003be54991f1db3bf24486a82e8ec22b28fcaabc102f6173c20a51da07c78e39115358bc2a48990dbdb668ac3705b54446484801a144b5b3fc2b9c62f434dc9e173ee59766181b34168650e048e9d3760acd7a9b4a8dd77c5f875830a763f22744fa8e27561eabfc32feff607c640b804e0d63ec';

const MANAGER_ID = 6697138; //
const MANUFACTURE_ID = 6885517; //

const AMO_MEASURER_FIELD_ID = 1166651; // id поля замерщик
const AMO_INSTALLER_FIELD_ID = 1166649; // id поля Монтажник

const MAIN_PIPELINE_ID = 3933427; // воронка

const MEASUREMENT_DATE_AGREED_STATUS_ID = 37586809; // Дата замера согласована
const INSTALLATION_STATUS_ID = 37586824; // Монтаж
const WAITING_FOR_PREPAYMENT_STATUS_ID = 37586815; // Ожидание предоплаты
const WAITING_FOR_FULL_PAYMENT_STATUS_ID = 39628756; // Ожидание остатка оплаты
const DEFECT_OR_REDO_STATUS_ID = 39628708; // Косяк или переделать
const NO_MONEY_OR_NO_CONTRACT_STATUS_ID = 37586812; // Замер есть, денег и договора нет
const ORDERING_MATERIALS_STATUS_ID = 39628084; // Заказ материала
const REPEATED_INSTALLATION_STATUS_ID = 41188609; // повторный монтаж
const QUALITY_CONTROL_STATUS_ID = 37586827; // Проверка качества
const CANCELING_THE_MEASUREMENT_STATUS_ID = 39676825; // Отмена замера

const MEASUREMENT_DATETIME_FIELD_ID = 1166655; // Дата замера
const PREPAYMENT_AMOUNT_FIELD_ID = 1171571; // Сумма предоплаты в руб
const REMAINS_PAYMENT_AMOUNT_FIELD_ID = 1171573; // Должны доплатить на монтаже
const PREPAYMENT_TYPE_FIELD_ID = 1166647; // Тип предоплаты
const REMAINS_PAYMENT_TYPE_FIELD_ID = 1171577; // Должны доплатить на монтаже

const MATERIAL_NAME_FIELD_ID = 1175413; // Должны доплатить на монтаже

const CASH_PAYMENT_METHOD = 1274043; // "Наличные"
const BANK_PAYMENT_METHOD = 1274041; // "Расчетный счет"
const TERMINAL_PAYMENT_METHOD = 1278947; // "Эквайринг (Терминал)"
const SBER_PAYMENT_METHOD = 1278949; // "Эквайринг (Терминал)"
