<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
class CategorySeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Очистка таблицы перед вставкой
      //   DB::table('categories')->truncate();

        $categories = [
            ["id" => 0, "name" => "Target"],
            ["id" => 1, "name" => "Smm"],
            ["id" => 2, "name" => "Copyright"],
            ["id" => 3, "name" => "Sites"],
            ["id" => 4, "name" => "Chat Bots"],
            ["id" => 5, "name" => "SEO"],
            ["id" => 6, "name" => "Design"],
            ["id" => 7, "name" => "Context"],
            ["id" => 8, "name" => "Produser"],
            ["id" => 9, "name" => "Marketing"],
            ["id" => 10, "name" => "Avitolog"],
            ["id" => 11, "name" => "Jurisprudence"],
            ["id" => 12, "name" => "Psychology"],
            ["id" => 13, "name" => "Surgery"],
            ["id" => 14, "name" => "Interior"],
            ["id" => 15, "name" => "Tutor"],
            ["id" => 1501, "name" => "Tutor English"],
            ["id" => 16, "name" => "Assistant"],
            ["id" => 17, "name" => "Marketplaces"],
            ["id" => 18, "name" => "Bots"],
            ["id" => 19, "name" => "Sales"],
            ["id" => 20, "name" => "Investments"],
            ["id" => 21, "name" => "Accountant"],
            ["id" => 22, "name" => "Nutrition"],
            ["id" => 23, "name" => "Marking"],
            ["id" => 24, "name" => "Cargo"],
            ["id" => 25, "name" => "Fullfillment"],
            ["id" => 26, "name" => "Analytics"],
            ["id" => 27, "name" => "Beautydubai"],
            ["id" => 28, "name" => "Metodology"],
            ["id" => 29, "name" => "Crops"],
            ["id" => 30, "name" => "Crypto"],
            ["id" => 31, "name" => "Certification"],
            ["id" => 32, "name" => "Engineer"],
            ["id" => 33, "name" => "Couch"],
            ["id" => 34, "name" => "Manager"],
            ["id" => 35, "name" => "Photographer"],
            ["id" => 36, "name" => "Property"],
            ["id" => 37, "name" => "Sugaring Depilation"],
            ["id" => 38, "name" => "Animator"],
            ["id" => 39, "name" => "Transportation"],
            ["id" => 40, "name" => "German"],
            ["id" => 41, "name" => "Dent"],
            ["id" => 42, "name" => "Reviews"],
            ["id" => 43, "name" => "Videograph Dubai"],
            ["id" => 44, "name" => "HRIT"],
            ["id" => 45, "name" => "PR"],
            ["id" => 46, "name" => "Vocals"],
            ["id" => 47, "name" => "Tailoring"],
            ["id" => 48, "name" => "Translate"],
            ["id" => 49, "name" => "Customs"],
            ["id" => 50, "name" => "Furniture"],
            ["id" => 51, "name" => "Music"],
            ["id" => 52, "name" => "Tech Fixing"],
            ["id" => 53, "name" => "Gen Wrokers"],
            ["id" => 54, "name" => "HR"],
            ["id" => 55, "name" => "Cleanning"],
            ["id" => 56, "name" => "Business Sale"],
            ["id" => 57, "name" => "Reelsmaker"],
            ["id" => 58, "name" => "Building"],
            ["id" => 59, "name" => "Astrology"],
            ["id" => 60, "name" => "Mailer"],
            ["id" => 61, "name" => "Appartment Fix"],
            ["id" => 62, "name" => "Dream"],
            ["id" => 63, "name" => "Dubai Event"],
        ];

      foreach ($categories as $categoryData) {
         Category::firstOrCreate(
             ['id' => $categoryData['id']],
             ['name' => $categoryData['name']]
         );
      }
    }
}
