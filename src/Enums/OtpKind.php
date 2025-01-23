<?php

namespace Upsoftware\Auth\Enums;

enum OtpKind: string
{
    case LOGIN = 'login';
    case REGISTER = 'register';
    case RESET = 'reset';
    case CHANGE_PASSWORD = 'change_password';
    case CHANGE_EMAIL = 'change_email';
    case CHANGE_PHONE = 'change_phone';
    case DELETE_ACCOUNT = 'delete_account';
}
