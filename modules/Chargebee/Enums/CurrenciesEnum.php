<?php

namespace Modules\Chargebee\Enums;

use App\Http\Traits\EnumToArray;

// Currencies which use in chargebee plan id names
enum CurrenciesEnum: string
{
    use EnumToArray;

    case AED = 'aed';
    case AUD = 'aud';
    case BGN = 'bgn';
    case BRL = 'brl';
    case CAD = 'cad';
    case CHF = 'chf';
    case CLP = 'clp';
    case CNY = 'cny';
    case COP = 'cop';
    case CZK = 'czk';
    case DKK = 'dkk';
    case EGP = 'egp';
    case EUR = 'eur';
    case GBP = 'gbp';
    case HKD = 'hkd';
    case HUF = 'huf';
    case IDR = 'idr';
    case ILS = 'ils';
    case INR = 'inr';
    case JPY = 'jpy';
    case KRW = 'krw';
    case KZT = 'kzt';
    case MXN = 'mxn';
    case MYR = 'myr';
    case NGN = 'ngn';
    case NOK = 'nok';
    case NZD = 'nzd';
    case PEN = 'pen';
    case PHP = 'php';
    case PKR = 'pkr';
    case PLN = 'pln';
    case QAR = 'qar';
    case RON = 'ron';
    case RUB = 'rub';
    case SAR = 'sar';
    case SEK = 'sek';
    case SGD = 'sgd';
    case THB = 'thb';
    case TRY = 'try';
    case TWD = 'twd';
    case TZS = 'tzs';
    case USD = 'usd';
    case VND = 'vnd';
    case ZAR = 'zar';

}
