<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Keyword;
use Illuminate\Database\Seeder;
use \Ramsey\Uuid\Uuid;

class KeywordsSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subToCategory = [
            36138 => 0,
            36139 => 0,
            36142 => 1,
            36143 => 1,
            36153 => 2,
            36147 => 3,
            36149 => 3,
            36150 => 3,
            36144 => 5,
            36145 => 5,
            36140 => 7,
            36141 => 7,
            36154 => 8,
            36155 => 10,
            36158 => 12,
            36159 => 12,
            36156 => 14,
            36157 => 14,
            36173 => 15,
            36174 => 15,
            36166 => 16,
            36167 => 16,
            40098 => 17,
            40099 => 17,
            36172 => 18,
            36177 => 19,
            36178 => 19,
            36179 => 20,
            36187 => 21,
            36189 => 22,
            36188 => 23,
            36185 => 24,
            36186 => 24,
            36184 => 25,
            36183 => 26,
            39669 => 26,
            39670 => 26,
            36190 => 28,
            36170 => 29,
            36171 => 29,
            36200 => 31,
            36201 => 31,
            36210 => 47,
            36211 => 47,
            36228 => 47,
            35929 => 53,
            35930 => 53,
            36175 => 57,
            36176 => 57,
            39359 => 61,
            39386 => 61,
            39387 => 61,
            39393 => 62,
            36164 => 1501,
            36165 => 1501,
        ];

        $json = json_decode(file_get_contents(__DIR__ . '/json/tgstat_subscriptions.json'), true);


        $keywords = [];

        foreach ($json['response']['subscriptions'] as $subscription) {
            $category_id = $subToCategory[$subscription['subscription_id']];
            $keywordsStr = $subscription['keyword']['q'];
            foreach (explode("|", $keywordsStr) as $keywordStr) {
                $keywordStr = trim($keywordStr, "\"");
                $keywords[] = [
                    'keyword' => mb_strtolower($keywordStr, 'UTF-8'),
                    'category_id' => $category_id,
                ];

            }

        }
        $keywords = array_unique($keywords, SORT_REGULAR);
        foreach ($keywords as $keywordData) {
            Keyword::create($keywordData);
        }


//        Category::insert($categories);
    }
}
