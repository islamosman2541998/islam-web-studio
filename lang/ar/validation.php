<?php

return [
    'required' => 'حقل :attribute مطلوب.', 'email' => 'أدخل بريدًا إلكترونيًا صحيحًا في :attribute.',
    'unique' => 'قيمة :attribute مستخدمة بالفعل.', 'exists' => 'القيمة المحددة في :attribute غير متاحة.',
    'string' => 'يجب أن يكون :attribute نصًا.', 'numeric' => 'يجب أن يكون :attribute رقمًا.', 'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'boolean' => 'قيمة :attribute غير صحيحة.', 'array' => 'يجب أن يكون :attribute قائمة.', 'in' => 'القيمة المحددة في :attribute غير صحيحة.',
    'regex' => 'صيغة :attribute غير صحيحة.', 'url' => 'أدخل رابطًا صحيحًا في :attribute.', 'date' => 'أدخل تاريخًا صحيحًا في :attribute.',
    'after_or_equal' => 'يجب أن يكون :attribute في تاريخ :date أو بعده.', 'accepted' => 'يجب الموافقة على :attribute.',
    'min' => ['string' => 'يجب ألا يقل :attribute عن :min أحرف.', 'numeric' => 'يجب ألا تقل قيمة :attribute عن :min.', 'array' => 'يجب اختيار :min عناصر على الأقل.', 'file' => 'يجب ألا يقل حجم :attribute عن :min كيلوبايت.'],
    'max' => ['string' => 'يجب ألا يتجاوز :attribute :max حرفًا.', 'numeric' => 'يجب ألا تتجاوز قيمة :attribute :max.', 'array' => 'يجب ألا يتجاوز عدد عناصر :attribute :max.', 'file' => 'يجب ألا يتجاوز حجم :attribute :max كيلوبايت.'],
    'file' => 'يجب أن يكون :attribute ملفًا صالحًا.', 'mimes' => 'نوع :attribute يجب أن يكون: :values.', 'mimetypes' => 'نوع :attribute يجب أن يكون: :values.',
    'uploaded' => 'تعذّر رفع :attribute. راجع حجم الملف وحاول مرة أخرى.', 'confirmed' => 'تأكيد :attribute غير متطابق.',
    'attributes' => [],
];
