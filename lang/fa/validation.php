<?php

return [

    /*
    |--------------------------------------------------------------------------
    | خطوط زبان اعتبارسنجی
    |--------------------------------------------------------------------------
    */

    'accepted' => 'فیلد :attribute باید پذیرفته شود.',
    'accepted_if' => 'فیلد :attribute در صورتی که :other برابر :value باشد باید پذیرفته شود.',
    'active_url' => 'فیلد :attribute باید یک آدرس اینترنتی معتبر باشد.',
    'after' => 'فیلد :attribute باید تاریخی بعد از :date باشد.',
    'after_or_equal' => 'فیلد :attribute باید تاریخی بعد یا مساوی :date باشد.',
    'alpha' => 'فیلد :attribute باید فقط شامل حروف باشد.',
    'alpha_dash' => 'فیلد :attribute باید فقط شامل حروف، اعداد، خط تیره و زیرخط باشد.',
    'alpha_num' => 'فیلد :attribute باید فقط شامل حروف و اعداد باشد.',
    'any_of' => 'فیلد :attribute نامعتبر است.',
    'array' => 'فیلد :attribute باید یک آرایه باشد.',
    'ascii' => 'فیلد :attribute باید فقط شامل کاراکترهای الفبایی و نمادهای تک‌بایتی باشد.',
    'before' => 'فیلد :attribute باید تاریخی قبل از :date باشد.',
    'before_or_equal' => 'فیلد :attribute باید تاریخی قبل یا مساوی :date باشد.',
    'between' => [
        'array' => 'فیلد :attribute باید بین :min و :max مورد داشته باشد.',
        'file' => 'حجم فیلد :attribute باید بین :min و :max کیلوبایت باشد.',
        'numeric' => 'مقدار فیلد :attribute باید بین :min و :max باشد.',
        'string' => 'فیلد :attribute باید بین :min و :max کاراکتر باشد.',
    ],
    'boolean' => 'فیلد :attribute باید درست یا نادرست باشد.',
    'can' => 'فیلد :attribute شامل مقدار غیرمجاز است.',
    'confirmed' => 'تکرار فیلد :attribute با آن مطابقت ندارد.',
    'contains' => 'فیلد :attribute مقدار مورد نیاز را ندارد.',
    'current_password' => 'رمز عبور وارد شده صحیح نیست.',
    'date' => 'فیلد :attribute باید یک تاریخ معتبر باشد.',
    'date_equals' => 'فیلد :attribute باید تاریخی برابر :date باشد.',
    'date_format' => 'فیلد :attribute باید با قالب :format مطابقت داشته باشد.',
    'decimal' => 'فیلد :attribute باید :decimal رقم اعشار داشته باشد.',
    'declined' => 'فیلد :attribute باید رد شود.',
    'declined_if' => 'فیلد :attribute در صورتی که :other برابر :value باشد باید رد شود.',
    'different' => 'فیلد :attribute و :other باید متفاوت باشند.',
    'digits' => 'فیلد :attribute باید :digits رقم باشد.',
    'digits_between' => 'فیلد :attribute باید بین :min و :max رقم باشد.',
    'dimensions' => 'ابعاد تصویر فیلد :attribute نامعتبر است.',
    'distinct' => 'فیلد :attribute دارای مقدار تکراری است.',
    'doesnt_contain' => 'فیلد :attribute نباید شامل هیچ‌کدام از موارد زیر باشد: :values.',
    'doesnt_end_with' => 'فیلد :attribute نباید با یکی از موارد زیر تمام شود: :values.',
    'doesnt_start_with' => 'فیلد :attribute نباید با یکی از موارد زیر شروع شود: :values.',
    'email' => 'فیلد :attribute باید یک آدرس ایمیل معتبر باشد.',
    'encoding' => 'فیلد :attribute باید با کدگذاری :encoding باشد.',
    'ends_with' => 'فیلد :attribute باید با یکی از موارد زیر تمام شود: :values.',
    'enum' => 'مقدار انتخاب‌شده برای :attribute نامعتبر است.',
    'exists' => 'مقدار انتخاب‌شده برای :attribute نامعتبر است.',
    'extensions' => 'فیلد :attribute باید یکی از پسوندهای زیر را داشته باشد: :values.',
    'file' => 'فیلد :attribute باید یک فایل باشد.',
    'filled' => 'فیلد :attribute باید مقدار داشته باشد.',
    'gt' => [
        'array' => 'فیلد :attribute باید بیش از :value مورد داشته باشد.',
        'file' => 'حجم فیلد :attribute باید بیشتر از :value کیلوبایت باشد.',
        'numeric' => 'مقدار فیلد :attribute باید بیشتر از :value باشد.',
        'string' => 'فیلد :attribute باید بیشتر از :value کاراکتر باشد.',
    ],
    'gte' => [
        'array' => 'فیلد :attribute باید :value مورد یا بیشتر داشته باشد.',
        'file' => 'حجم فیلد :attribute باید بیشتر یا مساوی :value کیلوبایت باشد.',
        'numeric' => 'مقدار فیلد :attribute باید بیشتر یا مساوی :value باشد.',
        'string' => 'فیلد :attribute باید بیشتر یا مساوی :value کاراکتر باشد.',
    ],
    'hex_color' => 'فیلد :attribute باید یک رنگ هگزادسیمال معتبر باشد.',
    'image' => 'فیلد :attribute باید یک تصویر باشد.',
    'in' => 'مقدار انتخاب‌شده برای :attribute نامعتبر است.',
    'in_array' => 'فیلد :attribute باید در :other وجود داشته باشد.',
    'in_array_keys' => 'فیلد :attribute باید حداقل یکی از کلیدهای زیر را داشته باشد: :values.',
    'integer' => 'فیلد :attribute باید یک عدد صحیح باشد.',
    'ip' => 'فیلد :attribute باید یک آدرس IP معتبر باشد.',
    'ipv4' => 'فیلد :attribute باید یک آدرس IPv4 معتبر باشد.',
    'ipv6' => 'فیلد :attribute باید یک آدرس IPv6 معتبر باشد.',
    'json' => 'فیلد :attribute باید یک رشته JSON معتبر باشد.',
    'list' => 'فیلد :attribute باید یک فهرست باشد.',
    'lowercase' => 'فیلد :attribute باید با حروف کوچک باشد.',
    'lt' => [
        'array' => 'فیلد :attribute باید کمتر از :value مورد داشته باشد.',
        'file' => 'حجم فیلد :attribute باید کمتر از :value کیلوبایت باشد.',
        'numeric' => 'مقدار فیلد :attribute باید کمتر از :value باشد.',
        'string' => 'فیلد :attribute باید کمتر از :value کاراکتر باشد.',
    ],
    'lte' => [
        'array' => 'فیلد :attribute نباید بیشتر از :value مورد داشته باشد.',
        'file' => 'حجم فیلد :attribute باید کمتر یا مساوی :value کیلوبایت باشد.',
        'numeric' => 'مقدار فیلد :attribute باید کمتر یا مساوی :value باشد.',
        'string' => 'فیلد :attribute باید کمتر یا مساوی :value کاراکتر باشد.',
    ],
    'mac_address' => 'فیلد :attribute باید یک آدرس MAC معتبر باشد.',
    'max' => [
        'array' => 'فیلد :attribute نباید بیشتر از :max مورد داشته باشد.',
        'file' => 'حجم فیلد :attribute نباید بیشتر از :max کیلوبایت باشد.',
        'numeric' => 'مقدار فیلد :attribute نباید بیشتر از :max باشد.',
        'string' => 'فیلد :attribute نباید بیشتر از :max کاراکتر باشد.',
    ],
    'max_digits' => 'فیلد :attribute نباید بیشتر از :max رقم داشته باشد.',
    'mimes' => 'فیلد :attribute باید فایلی از این نوع باشد: :values.',
    'mimetypes' => 'فیلد :attribute باید فایلی از این نوع باشد: :values.',
    'min' => [
        'array' => 'فیلد :attribute باید حداقل :min مورد داشته باشد.',
        'file' => 'حجم فیلد :attribute باید حداقل :min کیلوبایت باشد.',
        'numeric' => 'مقدار فیلد :attribute باید حداقل :min باشد.',
        'string' => 'فیلد :attribute باید حداقل :min کاراکتر باشد.',
    ],
    'min_digits' => 'فیلد :attribute باید حداقل :min رقم داشته باشد.',
    'missing' => 'فیلد :attribute نباید مقدار داشته باشد.',
    'missing_if' => 'فیلد :attribute در صورتی که :other برابر :value باشد نباید مقدار داشته باشد.',
    'missing_unless' => 'فیلد :attribute نباید مقدار داشته باشد مگر اینکه :other برابر :value باشد.',
    'missing_with' => 'فیلد :attribute در صورت وجود :values نباید مقدار داشته باشد.',
    'missing_with_all' => 'فیلد :attribute در صورت وجود همه :values نباید مقدار داشته باشد.',
    'multiple_of' => 'فیلد :attribute باید مضربی از :value باشد.',
    'not_in' => 'مقدار انتخاب‌شده برای :attribute نامعتبر است.',
    'not_regex' => 'قالب فیلد :attribute نامعتبر است.',
    'numeric' => 'فیلد :attribute باید عدد باشد.',
    'password' => [
        'letters' => 'فیلد :attribute باید حداقل شامل یک حرف باشد.',
        'mixed' => 'فیلد :attribute باید حداقل شامل یک حرف بزرگ و یک حرف کوچک باشد.',
        'numbers' => 'فیلد :attribute باید حداقل شامل یک عدد باشد.',
        'symbols' => 'فیلد :attribute باید حداقل شامل یک نماد (کاراکتر ویژه) باشد.',
        'uncompromised' => 'رمز عبور وارد شده در نشت داده‌ها دیده شده است. لطفاً رمز عبور دیگری انتخاب کنید.',
    ],
    'present' => 'فیلد :attribute باید وجود داشته باشد.',
    'present_if' => 'فیلد :attribute در صورتی که :other برابر :value باشد باید وجود داشته باشد.',
    'present_unless' => 'فیلد :attribute باید وجود داشته باشد مگر اینکه :other برابر :value باشد.',
    'present_with' => 'فیلد :attribute در صورت وجود :values باید وجود داشته باشد.',
    'present_with_all' => 'فیلد :attribute در صورت وجود همه :values باید وجود داشته باشد.',
    'prohibited' => 'فیلد :attribute ممنوع است.',
    'prohibited_if' => 'فیلد :attribute در صورتی که :other برابر :value باشد ممنوع است.',
    'prohibited_if_accepted' => 'فیلد :attribute در صورتی که :other پذیرفته شود ممنوع است.',
    'prohibited_if_declined' => 'فیلد :attribute در صورتی که :other رد شود ممنوع است.',
    'prohibited_unless' => 'فیلد :attribute ممنوع است مگر اینکه :other در :values باشد.',
    'prohibits' => 'فیلد :attribute اجازه وجود :other را نمی‌دهد.',
    'regex' => 'قالب فیلد :attribute نامعتبر است.',
    'required' => 'فیلد :attribute الزامی است.',
    'required_array_keys' => 'فیلد :attribute باید شامل این کلیدها باشد: :values.',
    'required_if' => 'فیلد :attribute در صورتی که :other برابر :value باشد الزامی است.',
    'required_if_accepted' => 'فیلد :attribute در صورتی که :other پذیرفته شود الزامی است.',
    'required_if_declined' => 'فیلد :attribute در صورتی که :other رد شود الزامی است.',
    'required_unless' => 'فیلد :attribute الزامی است مگر اینکه :other در :values باشد.',
    'required_with' => 'فیلد :attribute در صورت وجود :values الزامی است.',
    'required_with_all' => 'فیلد :attribute در صورت وجود همه :values الزامی است.',
    'required_without' => 'فیلد :attribute در صورت عدم وجود :values الزامی است.',
    'required_without_all' => 'فیلد :attribute در صورت عدم وجود همه :values الزامی است.',
    'same' => 'فیلد :attribute باید با :other مطابقت داشته باشد.',
    'size' => [
        'array' => 'فیلد :attribute باید :size مورد داشته باشد.',
        'file' => 'حجم فیلد :attribute باید :size کیلوبایت باشد.',
        'numeric' => 'مقدار فیلد :attribute باید :size باشد.',
        'string' => 'فیلد :attribute باید :size کاراکتر باشد.',
    ],
    'starts_with' => 'فیلد :attribute باید با یکی از موارد زیر شروع شود: :values.',
    'string' => 'فیلد :attribute باید رشته (متن) باشد.',
    'timezone' => 'فیلد :attribute باید یک منطقه زمانی معتبر باشد.',
    'unique' => 'این :attribute قبلاً استفاده شده است.',
    'uploaded' => 'بارگذاری فیلد :attribute ناموفق بود.',
    'uppercase' => 'فیلد :attribute باید با حروف بزرگ باشد.',
    'url' => 'فیلد :attribute باید یک آدرس اینترنتی معتبر باشد.',
    'ulid' => 'فیلد :attribute باید یک ULID معتبر باشد.',
    'uuid' => 'فیلد :attribute باید یک UUID معتبر باشد.',

    /*
    |--------------------------------------------------------------------------
    | پیام‌های سفارشی اعتبارسنجی
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'current_password' => [
            'current_password' => 'رمز عبور وارد شده با رمز عبور فعلی شما مطابقت ندارد.',
        ],
        'password' => [
            'confirmed' => 'تکرار رمز عبور جدید با آن مطابقت ندارد.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | نام‌های نمایشی فیلدها
    |--------------------------------------------------------------------------
    |
    | به جای نام فنی فیلد (مثل email یا password)، نام فارسی نمایش داده می‌شود.
    |
    */

    'attributes' => [
        'current_password' => 'رمز عبور فعلی',
        'password' => 'رمز عبور',
        'password_confirmation' => 'تکرار رمز عبور',
        'email' => 'ایمیل',
        'name' => 'نام',
    ],

];
