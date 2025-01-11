<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateController extends Controller
{
    public function translateText()
    {
        // إعداد GoogleTranslate لتحديد اللغة المطلوبة (مثل: الفرنسية)
        $tr = new GoogleTranslate('fr'); // يمكنك تغيير 'fr' إلى أي لغة تريد الترجمة إليها
    
        // النص المراد ترجمته
        $translatedText = $tr->translate('Hello, world!');
    
        // عرض النص المترجم
        return view('translation', ['translatedText' => $translatedText]);
    }
    
}
