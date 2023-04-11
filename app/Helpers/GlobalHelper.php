<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

const DATE_FORMAT = 'Y-m-d';
const TIME_DATE_FORMAT = 'Y-m-d H:i:s';
const PAGINATION_COUNT = 20;

if (!function_exists('generate_dir_from_model_name')) {
   /**
    * @param $model_name
    * @return bool|string
    */
   function generate_dir_from_model_name($model_name): bool|string {
      $dirPaths = explode('\\', $model_name);

      return end($dirPaths);
   }
}

if (!function_exists('to_time_format')) {
   /**
    * @param string $date
    * @return string
    */
   function to_time_format(string $date): string {
      return Carbon::parse($date)->format(DATE_FORMAT);
   }
}

if (!function_exists('calculateWidthsFromFile')) {
    /**
     * @param string $imageFile
     * @return array
     */
   function calculateWidthsFromFile(string $imageFile): array {
       static $image;

       if (!$image) {
           $image = \Spatie\Image\Image::load($imageFile);
       }

       return [$image->getWidth(), $image->getHeight()];
   }
}

if (!function_exists('parse_email')) {
    /**
     * @param string $email
     * @throws FileNotFoundException
     * @throws Throwable
     * @return array|bool
     */
    function parse_email(string $email): array|bool {

        $fileEmails = 'emails.json';

        $noFile = !File::exists($fileEmails);

        throw_if($noFile, new FileNotFoundException('Fail For Email Names Not Found !'));

        $emails = json_decode(File::get($fileEmails), true);
        $emailDomain = explode('@', $email)[1];

        foreach($emails as $email)
            if($email["domain_name"] == $emailDomain)
                return $email;

        return false;
    }
}

if (!function_exists('isFullName')) {
    /**
     * @param string $str
     * @return bool
     */
    function isFullName(string $str): bool {
        return $str == trim($str) && str_contains($str, ' ');
    }
}

if (!function_exists('uploadIfExistsFile')) {
   /**
    * @param array $data
    * @return bool
    */
   function existsUploadAbleFileInArray(array $data): bool {
      foreach ($data as $item) {

          if (is_array($item)) {
              foreach ($item as $value) {
                  if ($value instanceof UploadedFile) return true;
              }
          } else {
              if ($item instanceof UploadedFile) return true;
          }
      }

      return false;
   }
}

if (!function_exists('getQueries')) {
   /**
    * @param $builder
    * @return string
    */
   function getQueries($builder): string {
      $addSlashes = str_replace('?', "'?'", $builder->toSql());

      return vsprintf(str_replace('?', '%s', $addSlashes), $builder->getBindings());
   }
}

if (!function_exists('eloquentRelationMake')) {
   /**
    * @param $relation
    * @param array $data
    * @param string $field
    * @param string $method
    */
   function eloquentRelationMake($relation, array $data, string $field, string $method): void {
      if (isset($data[$field])) {
         $relation->{$field}()->{$method}($data[$field]);
      }
   }
}

if (!function_exists('formatSeconds')) {
    /**
     * @param $seconds
     * @return string
     */
    function formatSeconds($seconds): string {
        $hours = 0;
        $milliseconds = str_replace("0.", '', $seconds - floor($seconds));

        if ($seconds > 3600)
            $hours = floor($seconds / 3600);

        $seconds = $seconds % 3600;

        return str_pad($hours, 2, '0', STR_PAD_LEFT)
            . gmdate(':i:s', $seconds) . ($milliseconds ? ".$milliseconds" : '');
    }
}

if (!function_exists('existTranslation')) {
   /**
    * @param array $data
    * @param string $locale
    * @return bool
    */
   function existTranslation(array $data, string $locale): bool {
      return key_exists($locale, $data);
   }
}

if (!function_exists('deleteValue')) {
    /**
     * @param array $array
     * @param array $except
     * @return array
     */
    function deleteValue(array $array, array $except): array {
        return collect($array)->reject(function ($item) use ($except) {
            return in_array($item, $except);
        })->toArray();
    }
}

if (!function_exists('diff_seconds_echo')) {
   /**
    * @param $start
    * @return string
    */
   function diff_seconds_echo($start): string {
      return ' Seconds to execute: ' . (microtime(TRUE) - $start) * 1000;
   }
}

if (!function_exists('unset_value')) {
   /**
    * @param string $name
    * @param array $array
    * @return void
    */
   function unset_value(string $name, array &$array): void {
      if (($key = array_search($name, $array)) !== false) {
         unset($array[$key]);
      }
   }
}

if (!function_exists('is_valid_date')) {
   /**
    * Сравнивает дати, если выбранная дата менше текушую дату + 40 дней то вернет лож
    *
    * @param $comparableDate
    * @param int $diffDays
    * @return bool
    */
   function is_valid_date($comparableDate, int $diffDays): bool {
      $dateCompare = Carbon::now()->addDays($diffDays);

      return Carbon::parse($dateCompare)->diffInDays($comparableDate, FALSE) >= 0;
   }
}

if (!function_exists('user')) {
   /**
    * @param string $guardName
    * @return User|null
    */
   function user(string $guardName = 'api'): User|null {
      static $staticUser;

      if (!$staticUser) {
         $staticUser = auth($guardName)->user() ?? auth()->user();
      }

      return $staticUser;
   }
}

if (!function_exists('cache_forever')) {
   /**
    * Проверяет если есть такой ключ в кеше возврошает значение если нету добовляет после
    * чего снога возврошает значение сохроняет без срока
    * (Можете познакомится тут https://laravel.su/docs/8.x/cache#retrieving-items-from-the-cache)
    *
    * @param string $key
    * @param Closure $callBack
    * @return mixed
    */
   function cache_forever(string $key, Closure $callBack): mixed {
      return Cache::rememberForever($key, $callBack);
   }
}

if (!function_exists('cache_remember')) {
   /**
    * Выполнение замыкания с последующим сохранением и получением результата
    *
    * @param string $key
    * @param int $seconds
    * @param Closure $callBack
    * @return mixed
    */
   function cache_remember(string $key, Closure $callBack, int $seconds = 3600): mixed {
      return Cache::remember($key, $seconds, $callBack);
   }
}

if (!function_exists('setPermissionIfNoExists')) {
   /**
    * $permission 'string' or ['string', 'string']
    * Used Package Spatie Permissions https://spatie.be/docs/laravel-permission/v5/introduction
    * @param User $user
    * @param string|array $permission
    */
   function setPermissionIfNoExists(User $user, string|array $permission): void {
      if ($user) {
         if (!$user->hasPermissionTo($permission)) {
            $user->givePermissionTo($permission);
         }
      }
   }
}

if (!function_exists('dateCalculate')) {
   /**
    * возвращает разницу по формату (years/months/days/hours/minutes/seconds/milliseconds)
    *
    * @param $time (time() or date(format))
    * @param string $format
    * @param string $timezone
    * @return float|int|string
    */
   function dateCalculate($time, string $format = '', string $timezone = 'Asia/Yerevan'): float|int|string {
      $now = Carbon::now($timezone);
      $emitted = Carbon::parse($time, $timezone);

      switch ($format) {
      case 'years':
         return $now->diffInYears($emitted);
      case 'months':
         return $now->diffInMonths($emitted);
      case 'days':
         return $now->diffInDays($emitted);
      case 'hours':
         return $now->diffInHours($emitted);
      case 'minutes':
         return $now->diffInMinutes($emitted);
      case 'seconds':
         return $now->diffInSeconds($emitted);
      case 'milliseconds':
         return $now->diffInMilliseconds($emitted);
      default:
         return $now->diffForHumans($emitted);
      }
   }
}

if (!function_exists('isLocal')) {
   /**
    * @return bool
    */
   function isLocal(): bool {
       return app()->environment('local');
   }
}

if (!function_exists('makeSelectData')) {
   /**
    * @param $data
    * @param string $key
    * @param string $value
    * @return array
    */
   function makeSelectData($data, string $key, string $value): array {
      $array = [];
      $data->collect()->map(function ($item) use (&$array, $key, $value) {
         $array[$item->{$key}] = $item->{$value};
      });
      return $array;
   }
}
