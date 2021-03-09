<?php

namespace Dive\NovaTranslationEditor\Http\Controllers;

use Dive\NovaTranslationEditor\LanguageLine;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class TranslationUpdateController
{
    use ValidatesRequests;

    public function __invoke(Request $request)
    {
        $data = $this->validate($request, [
            'group' => 'string',
            'key' => 'string',
            'locale' => 'string|size:2',
            'text' => 'string',
        ]);

        $data['published_at'] = null;

        return response()->json(LanguageLine::updateOrCreate(collect($data)->only(['group', 'key', 'locale'])->toArray(), $data));
    }
}
