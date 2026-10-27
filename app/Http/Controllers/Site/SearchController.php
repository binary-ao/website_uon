<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __invoke(Request $request, SearchService $search)
    {
        $q = Str::limit(trim(strip_tags((string) $request->query('q', ''))), 100, '');
        $types = array_values(array_intersect((array) $request->query('tipo', []), array_keys(SearchService::TYPES)));
        $results = null;
        if (mb_strlen($q) >= 2) {
            $results = $search->search($q, $types, 10);
        }

        return view('public.search', [
            'q' => $q,
            'types' => $types,
            'results' => $results,
            'typeOptions' => SearchService::TYPES,
        ]);
    }
}
