<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateController extends Controller
{
    public function translateText()
    {
        
        $tr = new GoogleTranslate('fr'); 
    
        
        $translatedText = $tr->translate('Hello, world!');
    
        
        return view('translation', ['translatedText' => $translatedText]);
    }
    
}
