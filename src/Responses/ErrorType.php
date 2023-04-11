<?php

namespace LaravelNeverCrud\Responses;

use BenSampo\Enum\Enum;

final class ErrorType extends Enum
{
  const SERVER_ERROR = '00000';
  const CREATE_VALIDATION_FAILED = '00001';
  const UPDATE_VALIDATION_FAILED = '00002';
}
