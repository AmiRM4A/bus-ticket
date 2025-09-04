# سیستم مدیریت بلیط اتوبوس

## ۱. خلاصه کلی

**سیستم مدیریت بلیط اتوبوس** یک اپلیکیشن بک‌اند است که بر پایه فریمورک **لاراول** توسعه داده شده. این سیستم با بهره‌گیری از پکیج `nwidart/laravel-modules` توسعه یافته است.

این مستند با هدف **راه‌اندازی توسعه‌دهندگان جدید** تهیه شده است. در این مستند، ویژگی‌ها، فرآیندها و منطق کسب‌وکار با ارجاع مستقیم به موجودیت‌های پایگاه داده و سرویس‌های اصلی تشریح می‌شوند تا درک عمیقی از عملکرد سیستم ارائه گردد.

---

## ۲. ماژول‌ها و موجودیت‌های اصلی

### ۲.۱. ماژول کاربران

- **هدف:** مدیریت ثبت‌نام و احراز هویت کاربران سیستم.

- **جدول اصلی:** `users` (`id`, `name`, `email`, `password`)

- **احراز هویت:** از طریق **ایمیل و رمز عبور**. پس از ورود موفق، یک توکن Sanctum برای کاربر صادر می‌شود. (صرفاً لاگین/رجیستر عادی؛ سیستم نقش/سطوح دسترسی پوشش داده نمی‌شود.)


### ۲.۲. ماژول رانندگان

- **هدف:** مدیریت اطلاعات رانندگانی که به سفرها تخصیص داده می‌شوند.

- **جدول اصلی:** `drivers` (`id`, `name`, `mobile`)

- **روابط:** یک راننده می‌تواند به چندین اتوبوس تخصیص داده شود.


### ۲.۳. ماژول اتوبوس‌ها

- **هدف:** مدیریت اطلاعات اتوبوس‌ها و ساختار صندلی‌ها.

- **جداول اصلی:**

  - `buses` (`id`, `model`, `plate`, `seats_count`)

  - `bus_seats` (`id`, `bus_id`, `seat_number`)

- **منطق:** برای هر اتوبوس، مجموعه صندلی‌ها تعریف می‌شود. هر صندلی شامل ستون و ردیف است (ستون‌های A/B/C/D؛ مثال: A5). **دو صندلی با شماره یکسان نمی‌توان داشت.**


### ۲.۴. ماژول مکان‌ها

- **هدف:** تعریف مبدأ و مقصد سفرها.

- **جدول اصلی:** `provinces` (`id`, `name`)

- **استفاده:** هر سفر دارای `from_province_id` و `to_province_id` است.


### ۲.۵. ماژول سفرها

- **هدف:** مدیریت سفرها، صندلی‌ها و رزروها (هسته سیستم).

- **جداول اصلی:**

  - `trips` (`id`, `bus_id`, `from_province_id`, `to_province_id`, `departure_time`, `price_per_seat`)

  - `trip_seats` (`id`, `trip_id`, `bus_seat_id`, `status`, `reserved_gender`, `expires_at`) — برای رزرو موقت نیز استفاده می‌شود.

  - `trip_reservations` (`id`, `user_id`, `trip_seat_id`, `passenger_id`, `order_item_id`) — رزرو نهایی صندلی پس از پرداخت.

- **قوانین کلیدی:**

  - **انقضای رزرو:** رزرو موقت **۱۰ دقیقه** معتبر است و با Schedule هر دقیقه، رزروهای منقضی به `available` برمی‌گردند.

  - **محدودیت جنسیت:** رزرو صندلی مجاور با جنسیت مخالف ممنوع است (بر اساس ستون متفاوت در همان ردیف؛ چپ/راست). در صورت عدم تطابق با `reserved_gender` صندلی مجاور، رزرو رد می‌شود.

  - **کنترل همزمانی:** تمام عملیات درون **Transaction** انجام شده و ردیف‌های `trip_seats` مربوط قفل می‌شوند تا از Race Condition جلوگیری شود.


### ۲.۶. ماژول مسافران

- **هدف:** ذخیره اطلاعات مسافرانی که برای آن‌ها بلیط رزرو می‌شود.

- **جدول اصلی:** `passengers` (`id`, `first_name`, `last_name`, `mobile`, `national_code`, `gender`)

- **روابط:** هر مسافر می‌تواند چند بلیط در سفرهای مختلف داشته باشد.


### ۲.۷. ماژول سفارشات

- **هدف:** مدیریت فرآیند رزرو از ایجاد تا پرداخت.

- **جداول اصلی:**

  - `orders` (`id`, `user_id`, `status`) با وضعیت‌های `pending`, `completed`, `failed`, `cancelled`

  - `order_items` (`id`, `order_id`, `trip_seat_id`, `passenger_id`, `price`)

- **منطق لغو:** اگر **تمام** آیتم‌های یک سفارش لغو شوند، وضعیت سفارش به `cancelled` تغییر می‌کند؛ در غیر این صورت فقط آیتم‌های مربوطه لغو می‌شوند و سفارش قابل پرداخت می‌ماند.


### ۲.۸. ماژول پرداخت‌ها

- **هدف:** پردازش پرداخت سفارشات.

- **جدول اصلی:** `payments` (`id`, `order_id`, `transaction_id`, `amount`, `status`) با وضعیت‌های `pending`, `completed`, `failed`

- **فرآیند کلی:**

  1. ایجاد رکورد پرداخت `pending` و بازگرداندن لینک درگاه.

  2. بازگشت از درگاه و اعتبارسنجی پاسخ.

  3. **موفق:** پرداخت/سفارش `completed` و صندلی‌ها `sold`؛ ثبت در `trip_reservations`.

  4. **ناموفق:** پرداخت/سفارش `failed` و آزادسازی صندلی‌ها.


---

## ۳. فرآیند کامل کسب‌وکار

1. **احراز هویت:** کاربر ثبت‌نام/ورود کرده و **توکن** دریافت می‌کند.

2. **جستجوی سفر:** با فیلترهای مبدأ/مقصد/تاریخ لیست سفرها را می‌بیند.

3. **انتخاب سفر و صندلی:** نقشه صندلی‌ها و وضعیت هر کدام (available/reserved/sold) دریافت می‌شود.

4. **رزرو صندلی (موقت):**

  - قفل کردن رکوردهای صندلی‌های درخواستی در DB

  - تغییر وضعیت به `reserved` (در صورت عدم تداخل جنسیتی) و ست‌کردن `expires_at` (+۱۰ دقیقه)

  - ایجاد سفارش `pending` و بازگرداندن شناسه سفارش

5. **پرداخت:**

  - `order_id` به اندپوینت checkout داده می‌شود؛ لینک پرداخت برگردانده می‌شود؛ پس از پرداخت، کاربر به **callback** برمی‌گردد.

6. **تکمیل:**

  - **موفق:** تایید تراکنش، سفارش/پرداخت `completed`، صندلی‌ها `sold`، ثبت در `trip_reservations`.

  - **ناموفق یا انقضاء:** کاربر تا **۱۰ دقیقه** می‌تواند تلاش مجدد کند؛ در غیر این صورت صندلی‌ها آزاد و سفارش/پرداخت `failed` یا `cancelled` می‌شوند.


---

## ۴. مستندات دقیق اندپوینت‌ها (API)
### قالب استاندارد تمامی پاسخ‌ها (Response Structure)

تمام پاسخ‌ها در قالب استاندارد زیر هستند:
```json
{
  "status": "HTTP_STATUS_CODE",
  "message": "MESSAGE_OF_RESPONSE",
  "data": "NULL | DATA_OBJECT"
}
```

### پارامتر‌های مورد نیاز هر درخواست (Request Params)

هر درخواست پارامتر‌های خود را خواهد داشت (درصورت نیاز) که با استفاده از جداول مشخص شده است. درون جداول نام پارامتر (نامی که حین ارسال پارامتر باید استفاده شود)، نوع پارامتر، اجباری یا اختیاری بودن پارامتر و همینطور توضیحاتی راجب پارامتر آورده شده است.

#### پارامتر‌های نامعتبر:
درصورتی که پارامتر‌های ارسالی به یک اندپوینت نامعتبر باشد، پاسخ زیر دریافت خواهد شد:

پاسخ ناموفق (422):

```json
{
  "status": 422,
  "message": "اطلاعات ارسال شده نامعتبر است.",
  "data": {
    "errors": {...}
  }
}
```

بعنوان مثال:

```json
{
  "status": 422,
  "message": "اطلاعات ارسال شده نامعتبر است.",
  "data": {
    "errors": {
      "email": [
        "این ایمیل قبلا ثبت شده است."
      ],
      "password": [
        "رمز عبور باید حداقل ۸ کاراکتر باشد."
      ]
    }
  }
}
```

### نحوه‌ی اعتبارسنجی (Authentication)

اعتبارسنجی Token Base خواهد بود. یک توکن JWT از طریق هدر Authorization برای هر درخواست باید تنظیم شود.

درصورتی که اندپوینت مورد نظر نیاز به اعتبارسنجی (Auth) داشته باشد و توکن ارسال نشود، ساختار زیر بعنوان response دریافت خواهد شد:

پاسخ ناموفق‌ (401):
```json
{
  "message": "Unauthenticated."
}
```

---


### ۴.۱. ماژول کاربران

#### ثبت‌نام کاربر

- **Method:** POST

- **Path:** `v1/user/register`

| پارامتر               | نوع    | اجباری | توضیحات        |
| --------------------- | ------ | ------ | -------------- |
| name                  | string | ✓      | نام کاربر      |
| email                 | string | ✓      | ایمیل کاربر    |
| password              | string | ✓      | رمز عبور       |
| password_confirmation | string | ✓      | تکرار رمز عبور |

**پاسخ موفق (201):**

```json
{
  "status": 201,
  "message": "کاربر با موفقیت ثبت نام شد",
  "data": {
    "user": {
      "id": 1,
      "name": "علی رضایی",
      "email": "ali.rezaei@example.com",
      "created_at": "2025-09-03T10:00:00Z"
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ123456"
  }
}
```
#### ورود کاربر

- **Method:** POST

- **Path:** `v1/user/login`


|پارامتر|نوع|اجباری|توضیحات|
|---|---|---|---|
|email|string|✓|ایمیل کاربر|
|password|string|✓|رمز عبور|

**پاسخ موفق (200):**

```json
{
  "status": 200,
  "message": "ورود با موفقیت انجام شد",
  "data": {
    "user": {
      "id": 1,
      "name": "علی رضایی",
      "email": "ali.rezaei@example.com"
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ123456"
  }
}
```

**پاسخ خطا (401):**

```json
{
  "status": 401,
  "message": "ایمیل یا رمز عبور اشتباه است",
  "data": null
}
```

#### خروج کاربر

- **Method:** POST

- **Path:** `v1/user/logout`

- **احراز هویت:** مورد نیاز


**پاسخ موفق (200):**

```json
{
  "status": 200,
  "message": "خروج با موفقیت انجام شد",
  "data": null
}
```

#### اطلاعات کاربر فعلی

- **Method:** GET

- **Path:** `v1/user`

- **احراز هویت:** مورد نیاز


**پاسخ موفق (200):**

```json
{
  "status": 200,
  "message": "عملیات با موفقیت انجام شد",
  "data": {
    "id": 1,
    "name": "علی رضایی",
    "email": "ali.rezaei@example.com",
    "created_at": "2025-09-03T10:00:00Z"
  }
}
```

---

### ۴.۲. ماژول سفرها

#### لیست و فیلتر سفرها

- **Method:** GET

- **Path:** `v1/trips`

- **احراز هویت:** مورد نیاز


|پارامتر|نوع|اجباری|توضیحات|
|---|---|---|---|
|bus_id|integer|-|فیلتر بر اساس شناسه اتوبوس|
|from_province_id|integer|-|فیلتر مبدأ|
|to_province_id|integer|-|فیلتر مقصد|
|min_price|decimal|-|حداقل قیمت|
|max_price|decimal|-|حداکثر قیمت|
|per_page|integer|-|پیش‌فرض: ۲۰|

**پاسخ موفق (200):**

```json
{
  "status": 200,
  "message": "عملیات با موفقیت انجام شد",
  "data": {
    "items": [
      {
        "id": 1,
        "bus": {
          "id": 1,
          "model": "ولوو B9R",
          "plate": "۱۲ع۳۴۵-۶۷",
          "seats_count": 44
        },
        "origin": {
          "id": 1,
          "name": "تهران"
        },
        "destination": {
          "id": 2,
          "name": "اصفهان"
        },
        "total_seats": 44,
        "reserved_seats_count": 0,
        "trip_date": "2025-10-20",
        "departure_time": "23:00:00",
        "arrived_at": null,
        "created_at": "2025-09-03T12:00:00Z",
        "updated_at": "2025-09-03T12:00:00Z"
      }
    ],
    "links": {
      "first": "https://api.yourdomain.com/api/v1/trips?page=1",
      "last": "https://api.yourdomain.com/api/v1/trips?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 1,
      "links": [
        {
          "url": null,
          "label": "&laquo; Previous",
          "page": null,
          "active": false
        },
        {
          "url": "https://api.yourdomain.com/api/v1/trips?page=1",
          "label": "1",
          "page": 1,
          "active": true
        },
        {
          "url": null,
          "label": "Next &raquo;",
          "page": null,
          "active": false
        }
      ],
      "path": "https://api.yourdomain.com/api/v1/trips",
      "per_page": 20,
      "to": 15,
      "total": 15
    }
  }
}
```

#### ایجاد رزرو (رزرو موقت)

- **Method:** POST

- **Path:** `v1/trips`

- **احراز هویت:** مورد نیاز


|پارامتر|نوع|اجباری|توضیحات|
|---|---|---|---|
|trip_id|integer|✓|شناسه سفر|
|passengers|array|✓|آرایه مسافران|
|trip_seat_id|integer|✓|شناسه صندلی سفر (برای هر مسافر)|
|first_name|string|✓|نام|
|last_name|string|✓|نام خانوادگی|
|mobile|string|✓|موبایل|
|national_code|string|✓|کد ملی|
|gender|integer|✓|۰: زن، ۱: مرد|

**پاسخ موفق (201):**

```json
{
  "status": 201,
  "message": "رزرو با موفقیت ایجاد شد",
  "data": {
    "id": 1,
    "status": "pending",
    "total_amount": 450000,
    "created_at": "2025-09-03T14:10:00Z",
  }
}
```

**پاسخ ناموفق (400):**

```json
{
  "status": 400,
  "message": "صندلی 5 دیگر در دسترس نیست",
  "data": null
}
```

**پاسخ ناموفق (400):**

```json
{
  "status": 400,
  "message": "صندلی x به دلیل تداخل سیاست جنسیتی با صندلی مجاور y قابل رزرو نیست",
  "data": null
}
```

**پاسخ ناموفق (409):**

```json
{
  "status": 409,
  "message": "صندلی x به دلیل تداخل سیاست جنسیتی با صندلی مجاور y قابل رزرو نیست",
  "data": null
}
```



#### نمایش جزئیات یک سفر

- **Method:** GET

- **Path:** `v1/trips/{trip_id}`

- **احراز هویت:** مورد نیاز


**پاسخ موفق (200):**

```json
{
  "status": 200,
  "message": "عملیات با موفقیت انجام شد",
  "data": {
    "id": 1,
    "bus": {
      "id": 1,
      "model": "ولوو B9R",
      "plate": "۱۲ع۳۴۵-۶۷",
      "seats_count": 44
    },
    "seats": [
      {
        "id": 1,
        "status": "AVAILABLE",
        "name": "صندلی ۱",
        "row": 1,
        "column": "A",
        "reserved_gender": null,
        "is_available": true,
        "is_reserved": false,
        "is_sold": false
      },
      {
        "id": 2,
        "status": "SOLD",
        "name": "صندلی ۲",
        "row": 1,
        "column": "B",
        "number": "B1",
        "reserved_gender": "مرد",
        "is_available": false,
        "is_reserved": false,
        "is_sold": true
      }
    ],
    "origin": {
      "id": 1,
      "name": "تهران"
    },
    "destination": {
      "id": 2,
      "name": "اصفهان"
    },
    "total_seats": 44,
    "reserved_seats_count": 0,
    "trip_date": "2025-10-20",
    "departure_time": "23:00:00",
    "arrived_at": null,
    "created_at": "2025-09-03T12:00:00Z",
    "updated_at": "2025-09-03T12:00:00Z"
  }
}
```

#### لغو رزرو (آزادسازی صندلی‌های رزروشده)

- **Method:** DELETE

- **Path:** `v1/trips/reservations/{order_id}`

- **احراز هویت:** مورد نیاز


| پارامتر  | نوع   | اجباری | توضیحات                                                                                      |
| -------- | ----- | ------ | -------------------------------------------------------------------------------------------- |
| seat_ids | array | x      | آرایه آیدی صندلی‌های سفر برای لغو رزرو (اگر ارسال نشود، تمامی صندلی‌های سفارش لغو خواهند شد) |

**پاسخ موفق (200):**

```json
{
  "status": 200,
  "message": "صندلی‌های انتخاب شده با موفقیت لغو شدند",
  "data": null
}
```

---

### ۴.۳. ماژول سفارشات

#### نمایش جزئیات سفارش

- **Method:** GET

- **Path:** `v1/orders/{order_id}`

- **احراز هویت:** مورد نیاز


|پارامتر|نوع|اجباری|توضیحات|
|---|---|---|---|
|order|integer|✓|شناسه سفارش|

**پاسخ موفق (200):**

```json
{
  "status": 200,
  "message": "عملیات با موفقیت انجام شد",
  "data": {
    "id": 1,
    "status": "pending",
    "total_amount": 900000,
    "created_at": "2025-09-03T14:00:00Z",
    "expires_at": "2025-09-03T14:10:00Z",
    "items": [
      {
        "trip_seat_id": 5,
        "seat_number": "5A",
        "passenger": {
          "first_name": "سارا",
          "last_name": "محمدی",
          "national_code": "0012345678"
        },
        "price": 450000
      },
      {
        "trip_seat_id": 6,
        "seat_number": "5B",
        "passenger": {
          "first_name": "علی",
          "last_name": "رضایی",
          "national_code": "0087654321"
        },
        "price": 450000
      }
    ],
    "trip": {
      "from_province": "تهران",
      "to_province": "اصفهان",
      "trip_date": "2025-10-20",
      "departure_time": "23:00:00"
    }
  }
}
```

#### پرداخت سفارش (Checkout)

- **Method:** GET

- **Path:** `v1/orders/checkout/{order_id}`

- **احراز هویت:** مورد نیاز


**پاسخ موفق (200):**

```json
{
  "status": 200,
  "message": "عملیات با موفقیت انجام شد",
  "data": {
    "payment_url": "https://gateway.example.com/pay/a1b2c3d4e5f6",
    "transaction_id": "c69186cb-6a3f-41f1-8538-1d0fa71e5268a",
    "amount": 900000
  }
}
```

---

### ۴.۴. ماژول پرداخت‌ها

#### تایید پرداخت (Callback)

- **Method:** GET

- **Path:** `v1/payments/callback/{transaction_id}`

- **احراز هویت:** غیرضروری


**پاسخ موفق (200):**

```json
{
  "status": 200,
  "message": "پرداخت با موفقیت تایید شد",
  "data": {
    "id": 1,
    "status": "completed",
    "paid_at": "2025-09-03T14:05:15Z"
  }
}
```

**پاسخ خطا (422):**

```json
{
  "status": 404,
  "message": "تراکنش نامعتبر است یا درحال حاضر تایید شده است.",
  "data": null
}
```