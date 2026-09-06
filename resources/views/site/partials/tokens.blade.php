@php
    $tokens = ['primary'=>'#0A3323','secondary'=>'#105666','accent'=>'#839958','warm'=>'#D3968C','beige'=>'#F7F4D5','light_background'=>'#F7F4D5','light_surface'=>'#FFFDF4','light_text'=>'#0A3323','dark_background'=>'#071F17','dark_surface'=>'#0A3323','dark_text'=>'#F7F4D5','dark_link'=>'#A9D3C8'];
    $arabicFont = \App\Support\FontRegistry::arabic(\App\Support\Studio::setting('design.arabic_font'));
    $englishFont = \App\Support\FontRegistry::english(\App\Support\Studio::setting('design.english_font'));
@endphp
<style>:root{@foreach($tokens as $key=>$fallback)--{{ str_replace('_','-',$key) }}:{{ \App\Support\Studio::color(\App\Support\Studio::setting('design.'.$key),$fallback) }};@endforeach --canvas:var(--light-background);--surface:var(--light-surface);--ink:var(--light-text);--link:var(--secondary);--arabic-font:'{{ $arabicFont }}';--english-font:'{{ $englishFont }}';--display-font:var(--english-font);}[data-theme=dark]{--canvas:var(--dark-background);--surface:var(--dark-surface);--ink:var(--dark-text);--link:var(--dark-link);}</style>
