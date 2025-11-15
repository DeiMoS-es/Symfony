<?php
namespace App\Module\Movie\Exception;

class TmdbException extends \RuntimeException {}

class TmdbUnauthorizedException extends TmdbException {}
class TmdbNotFoundException extends TmdbException {}
class TmdbUnavailableException extends TmdbException {}
